<?php
// app/services/UserService.php

namespace App\Services;

require_once dirname(__FILE__) . '/../repositories/UserRepository.php';
require_once dirname(__FILE__) . '/../models/User.php';
require_once dirname(__FILE__) . '/../utils/Mailer.php'; // NEW: Include Mailer utility

use App\Repositories\UserRepository;
use App\Models\User;
use App\Utils\Mailer; // NEW: Use Mailer class

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
     * Authenticates a user and generates a token (simplified for now).
     * @param string $identifier User's username or email.
     * @param string $password User's plain text password.
     * @return array|false Returns an array with 'user' (User model) and 'token' on success, false on failure.
     */
    public function loginUser(string $identifier, string $password) {
        $userData = $this->userRepository->findByUsername($identifier); // Or findByEmail

        if (!$userData) {
            error_log("UserService: Login failed - User not found for identifier: " . $identifier);
            return false;
        }

        $user = new User($userData);

        if (!password_verify($password, $user->password_hash)) {
            error_log("UserService: Login failed - Invalid password for user: " . $user->username);
            return false;
        }

        $token = bin2hex(random_bytes(32));

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
            return new User($userData);
        }
        error_log("UserService: User profile not found for ID: " . $userId);
        return false;
    }
}
