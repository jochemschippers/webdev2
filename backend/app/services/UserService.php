<?php
// app/services/UserService.php

namespace App\Services;

require_once dirname(__FILE__) . '/../repositories/UserRepository.php';
require_once dirname(__FILE__) . '/../models/User.php';

use App\Repositories\UserRepository;
use App\Models\User;

class UserService {
    private $userRepository;
    public function __construct() {
        // UserRepository's constructor now handles its own database connection via the base Repository
        $this->userRepository = new UserRepository();
    }

    public function registerUser(string $username, string $email, string $password, string $role = 'customer') {
        // Basic validation
        if (empty($username) || empty($email) || empty($password)) {
            return false;
        }

        // Check if username or email already exists
        if ($this->userRepository->findByUsername($username)) {
            return false; // Username already exists
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $userData = [
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role
        ];

        $newUserId = $this->userRepository->createUser($userData);

        if ($newUserId) {
            $createdUser = $this->userRepository->getUserById($newUserId); // Fetch complete user data
            if ($createdUser) {
                return new User($createdUser); // Return a User model instance
            }
        }
        return false;
    }

    /**
     * Authenticates a user and generates a token (simplified for now).
     * @param string $identifier User's username or email.
     * @param string $password User's plain text password.
     * @return array|false Returns an array with 'user' (User model) and 'token' on success, false on failure.
     */
    public function loginUser(string $identifier, string $password) {
        // Try to find user by username (or email, if your findByUsername also searches email)
        $userData = $this->userRepository->findByUsername($identifier);

        if (!$userData) {
            return false; // User not found
        }

        $user = new User($userData);

        // Verify password
        if (!password_verify($password, $user->password_hash)) {
            return false; // Invalid password
        }

        // --- Simplified Token Generation (for demonstration) ---
        // In a real application, you'd use JWT or a more robust token system.
        // For now, let's generate a simple, long random string.
        $token = bin2hex(random_bytes(32)); // 64-character hex string

        // In a real app, you might save this token to the database linked to the user,
        // set an expiration, etc. For this basic setup, it's just a placeholder.

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
        $userData = $this->userRepository->find($userId);
        if ($userData) {
            return new User($userData);
        }
        return false;
    }
}
?>
