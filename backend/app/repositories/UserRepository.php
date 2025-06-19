<?php
// app/repositories/UserRepository.php

namespace App\Repositories; // Define a namespace for repositories
use \PDO; // Import PDO from the global namespace
use \PDOException; // Import PDOException from the global namespace

require_once dirname(__FILE__) . '/../models/User.php'; // Model for data structure
require_once __DIR__ . '/Repository.php'; // Require the base Repository class

class UserRepository extends Repository {
    // The $this->connection is now inherited from the base Repository

    /**
     * Constructor for UserRepository.
     * Calls the parent constructor to establish the database connection.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Finds a user by username.
     * @param string $username
     * @return array|false Returns associative array of user data if found, false otherwise.
     */
    public function findByUsername($username) {
        $query = "SELECT id, username, email, password_hash, role FROM users WHERE username = :username LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Finds a user by ID.
     * @param int $id
     * @return array|false Returns associative array of user data if found, false otherwise.
     */
    public function getUserById($id) {
        $query = "SELECT id, username, email, password_hash, role FROM users WHERE id = ? LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function createUser(array $userData) {
        $query = "INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)";
        $stmt = $this->connection->prepare($query);

        // Sanitize data before binding
        $username = htmlspecialchars(strip_tags($userData['username']));
        $email = htmlspecialchars(strip_tags($userData['email']));
        $password_hash = htmlspecialchars(strip_tags($userData['password_hash']));
        $role = htmlspecialchars(strip_tags($userData['role'] ?? 'customer')); // Default role to customer

        // Bind values
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password_hash", $password_hash);
        $stmt->bindParam(":role", $role);

        if ($stmt->execute()) {
            return (int)$this->connection->lastInsertId();
        }
        return false;
    }

    /**
     * Updates an existing user in the database.
     * @param int $id The ID of the user to update.
     * @param array $data Associative array of user data to update.
     * @return bool True on success, false on failure.
     */
    public function updateUser($id, array $data) {
        $query = "UPDATE users SET username = :username, email = :email, password_hash = :password_hash, role = :role WHERE id = :id";
        $stmt = $this->connection->prepare($query);

        // Sanitize data
        $username = htmlspecialchars(strip_tags($data['username']));
        $email = htmlspecialchars(strip_tags($data['email']));
        $password_hash = htmlspecialchars(strip_tags($data['password_hash']));
        $role = htmlspecialchars(strip_tags($data['role']));

        // Bind values
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password_hash", $password_hash);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Deletes a user from the database.
     * @param int $id The ID of the user to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteUser($id) {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
