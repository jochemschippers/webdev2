<?php
// app/services/UserService.php

namespace App\Services;

require_once dirname(__FILE__) . '/../repositories/UserRepository.php';
require_once dirname(__FILE__) . '/../models/User.php';
require_once dirname(__FILE__) . '/../utils/Mailer.php';
require_once dirname(__FILE__) . '/../utils/JWTUtility.php'; // NEW: Include JWTUtility

use App\Repositories\UserRepository;
use App\Models\User;
use App\Utils\Mailer;
use App\Utils\JWTUtility; // NEW: Use JWTUtility class

class UserService {
    private $userRepository;

    /**
     * Constructor for UserService.
     * Initializes the UserRepository.
     */
    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    /**
     * Retrieves all users.
     * @return array An array of User model instances.
     */
    public function getAllUsers() {
        $usersData = $this->userRepository->getAll();
        $users = [];
        foreach ($usersData as $data) {
            // Do not expose password_hash when fetching all users for display
            unset($data['password_hash']);
            $users[] = new User($data);
        }
        return $users;
    }

    /**
     * Registers a new user.
     * @param string $username
     * @param string $email
     * @param string $password
     * @param string $role
     * @return User|false Returns a User model instance on successful registration, false otherwise.
     */
    public function registerUser(string $username, string $email, string $password, string $role = 'customer') {
        // Basic validation
        if (empty($username) || empty($email) || empty($password)) {
            error_log("UserService: Registration failed - Missing required fields.");
            return false;
        }

        // --- NEW PASSWORD REQUIREMENTS ---
        // Minimum 8 characters
        if (strlen($password) < 8) {
            error_log("UserService: Registration failed - Password must be at least 8 characters long.");
            return false;
        }
        // At least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            error_log("UserService: Registration failed - Password must contain at least one uppercase letter.");
            return false;
        }
        // At least one lowercase letter (optional, but good practice if not covered by [A-Za-z] in other checks)
        if (!preg_match('/[a-z]/', $password)) {
            error_log("UserService: Registration failed - Password must contain at least one lowercase letter.");
            return false;
        }
        // At least one number
        if (!preg_match('/[0-9]/', $password)) {
            error_log("UserService: Registration failed - Password must contain at least one number.");
            return false;
        }
        // At least one special character (e.g., !@#$%^&*()-_+=)
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            error_log("UserService: Registration failed - Password must contain at least one special character.");
            return false;
        }
        // --- END NEW PASSWORD REQUIREMENTS ---


        // Check if username already exists
        if ($this->userRepository->findByUsername($username)) {
            error_log("UserService: Registration failed - Username already exists: " . $username);
            return false; // Username already exists
        }
        // Consider adding check for existing email: $this->userRepository->findByEmail($email)

        // Hash the password securely
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $userData = [
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role
        ];

        $newUserId = $this->userRepository->createUser($userData);

        if ($newUserId) {
            $createdUser = $this->userRepository->getUserById($newUserId);
            if ($createdUser) {
                $userModel = new User($createdUser);

                // NEW: Send welcome email
                $subject = "Welcome to GPU Shop!";
                $messageBody = "Thank you for registering, {$userModel->username}!<br><br>Your account has been successfully created. You can now log in and start browsing our graphic cards.";
                Mailer::sendEmail($userModel->email, $subject, $messageBody); // Call the static sendEmail method

                return $userModel; // Return a User model instance
            }
        }
        error_log("UserService: Registration failed - Database create user failed or user not found after creation.");
        return false;
    }

    /**
     * Authenticates a user and generates a token.
     * @param string $identifier User's username or email.
     * @param string $password User's plain text password.
     * @return array|false Returns an array with 'user' (User model) and 'token' on success, false on failure.
     */
    public function loginUser(string $identifier, string $password) {
        // Try to find by username first, then by email if not found
        $userData = $this->userRepository->findByUsername($identifier);
        if (!$userData) {
            $userData = $this->userRepository->findByEmail($identifier);
        }

        if (!$userData) {
            error_log("UserService: Login failed - User not found for identifier: " . $identifier);
            return false;
        }

        $user = new User($userData);

        if (!password_verify($password, $user->password_hash)) {
            error_log("UserService: Login failed - Invalid password for user: " . $user->username);
            return false;
        }

        // NEW: Generate a JWT upon successful login
        $jwtPayload = [
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role
        ];
        $token = JWTUtility::generateToken($jwtPayload, 60 * 24); // Token valid for 24 hours

        if (empty($token)) {
            error_log("UserService: Login failed - Failed to generate JWT.");
            return false;
        }

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    /**
     * Retrieves user profile by ID.
     * @param int $userId
     * @return User|false Returns User model instance if found, false otherwise.
     */
    public function getUserProfile(int $userId) {
        $userData = $this->userRepository->getUserById($userId);
        if ($userData) {
            // Do not expose password_hash when retrieving profile
            unset($userData['password_hash']);
            return new User($userData);
        }
        error_log("UserService: User profile not found for ID: " . $userId);
        return false;
    }

    /**
     * Updates an existing user's details.
     * @param int $id The ID of the user to update.
     * @param array $data Associative array of user data to update.
     * Allowed keys: 'username', 'email', 'password', 'role'.
     * @return User|false Returns the updated User model instance on success, false on failure.
     */
    public function updateUser(int $id, array $data) {
        $updateData = [];

        // Validate and sanitize inputs
        if (isset($data['username']) && !empty($data['username'])) {
            $updateData['username'] = $data['username'];
        }
        if (isset($data['email']) && !empty($data['email'])) {
            $updateData['email'] = $data['email'];
        }
        if (isset($data['role']) && in_array($data['role'], ['customer', 'admin'])) {
            $updateData['role'] = $data['role'];
        }

        // Handle password change: only if 'password' key is provided and not empty
        if (isset($data['password']) && !empty($data['password'])) {
            // Apply password strength requirements to new password
            $newPassword = $data['password'];
            if (strlen($newPassword) < 8 ||
                !preg_match('/[A-Z]/', $newPassword) ||
                !preg_match('/[a-z]/', $newPassword) ||
                !preg_match('/[0-9]/', $newPassword) ||
                !preg_match('/[^A-Za-z0-9]/', $newPassword)) {
                error_log("UserService: Update failed - New password does not meet complexity requirements.");
                return false; // Password not strong enough
            }
            $updateData['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        if (empty($updateData)) {
            error_log("UserService: No valid data provided for user update (ID: " . $id . ").");
            return false; // No valid fields to update
        }

        $success = $this->userRepository->update($id, $updateData);

        if ($success) {
            return $this->getUserProfile($id); // Fetch and return updated user data (without password hash)
        }
        error_log("UserService: Failed to update user in repository (ID: " . $id . ").");
        return false;
    }

    /**
     * Deletes a user.
     * @param int $id The ID of the user to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteUser(int $id): bool {
        // Prevent deleting the very first admin user (ID 1), or implement more robust logic.
        // For demonstration, a simple check.
        if ($id === 1) { // Assuming ID 1 is a critical admin account
            error_log("UserService: Attempted to delete protected user ID: " . $id);
            return false; // Cannot delete this user
        }
        return $this->userRepository->delete($id);
    }
}
