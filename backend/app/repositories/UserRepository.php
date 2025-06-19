<?php
// app/repositories/UserRepository.php

require_once dirname(__FILE__) . '/../models/User.php'; // Adjust path as necessary

class UserRepository {
    private $userModel;

    public function __construct(User $userModel) {
        $this->userModel = $userModel;
    }

    /**
     * Finds a user by username.
     * @param string $username
     * @return User|false Returns User object if found, false otherwise.
     */
    public function findByUsername($username) {
        return $this->userModel->findByUsername($username);
    }

    /**
     * Creates a new user.
     * @param array $userData Associative array of user data (username, email, password_hash, role).
     * @return User|false Returns the created User object on success, false on failure.
     */
    public function create(array $userData) {
        $this->userModel->username = $userData['username'];
        $this->userModel->email = $userData['email'];
        $this->userModel->password_hash = $userData['password_hash'];
        $this->userModel->role = $userData['role'] ?? 'customer'; // Default role to customer

        if ($this->userModel->create()) {
            return $this->userModel; // Return the model with the new ID
        }
        return false;
    }

    // You can add more methods here like getUserById, update, delete if needed for admin functions
}
?>
