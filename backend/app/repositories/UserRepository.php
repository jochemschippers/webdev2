<?php
// app/repositories/UserRepository.php

namespace App\Repositories;

use \PDO; // Import PDO from the global namespace
use \PDOException; // Import PDOException from the global namespace

require_once dirname(__FILE__) . '/../models/User.php';
require_once __DIR__ . '/Repository.php';

class UserRepository extends Repository {
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
        // Trim whitespace from the username to prevent issues with leading/trailing spaces
        $username = trim($username);

        $query = "SELECT id, username, email, password_hash, role FROM users WHERE username = ? LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        // Explicitly bind as a string (PDO::PARAM_STR)
        $stmt->bindParam(1, $username, PDO::PARAM_STR);
        $stmt->execute();
        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * Finds a user by email.
     * @param string $email
     * @return array|false Returns associative array of user data if found, false otherwise.
     */
    public function findByEmail($email) {
        // Trim whitespace from the email to prevent issues with leading/trailing spaces
        $email = trim($email);

        $query = "SELECT id, username, email, password_hash, role FROM users WHERE email = ? LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        // Explicitly bind as a string (PDO::PARAM_STR)
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->execute();
        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
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

    /**
     * Creates a new user in the database.
     * @param array $userData Associative array of user data (username, email, password_hash, role).
     * @return int|false Returns the ID of the newly created user on success, false on failure.
     */
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
    public function update($id, array $data) {
        $setParts = [];
        $params = [':id' => $id];

        if (isset($data['username'])) {
            $setParts[] = 'username = :username';
            $params[':username'] = htmlspecialchars(strip_tags($data['username']));
        }
        if (isset($data['email'])) {
            $setParts[] = 'email = :email';
            $params[':email'] = htmlspecialchars(strip_tags($data['email']));
        }
        if (isset($data['password_hash'])) {
            $setParts[] = 'password_hash = :password_hash';
            $params[':password_hash'] = htmlspecialchars(strip_tags($data['password_hash']));
        }
        if (isset($data['role'])) {
            $setParts[] = 'role = :role';
            $params[':role'] = htmlspecialchars(strip_tags($data['role']));
        }

        if (empty($setParts)) {
            error_log("UserRepository: No valid fields to update for user ID: " . $id);
            return false;
        }

        $query = "UPDATE users SET " . implode(', ', $setParts) . ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->connection->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

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
    public function delete($id) {
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
