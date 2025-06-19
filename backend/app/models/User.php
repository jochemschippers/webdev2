<?php
// app/models/User.php

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $role;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Finds a user by username.
     * @param string $username
     * @return User|false Returns User object if found, false otherwise.
     */
    public function findByUsername($username) {
        $query = "SELECT id, username, email, password_hash, role FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->role = $row['role'];
            return $this;
        }
        return false;
    }

    /**
     * Creates a new user.
     * @return bool True on success, false on failure.
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)";
        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password_hash = htmlspecialchars(strip_tags($this->password_hash));
        $this->role = htmlspecialchars(strip_tags($this->role));

        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":role", $this->role);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId(); // Set the ID of the newly created user
            return true;
        }
        return false;
    }
}
?>