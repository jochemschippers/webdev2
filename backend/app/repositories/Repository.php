<?php
// app/repositories/Repository.php

namespace App\Repositories;

use \PDO;
use \PDOException;

class Repository
{
    protected $connection; // Holds the PDO database connection

    /**
     * Constructor establishes the database connection.
     * It relies on dbconfig.php for credentials.
     */
    public function __construct()
    {
        // Corrected path to dbconfig.php
        require_once __DIR__ . '/../../dbconfig.php';

        $type = 'mysql'; // Database type
        $servername = \DB_HOST;
        $database = \DB_NAME;
        $username = \DB_USER;
        $password = \DB_PASS;

        try {
            $this->connection = new PDO("$type:host=$servername;dbname=$database", $username, $password);
            // Set PDO attributes for error handling and fetching
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // For real prepared statements
            $this->connection->exec("SET NAMES utf8mb4"); // Ensure UTF-8 support
        } catch (PDOException $e) {
            // Log the error and send a generic error response for security
            error_log("Database connection failed in Repository: " . $e->getMessage());
            http_response_code(500); // Internal Server Error
            echo json_encode(["message" => "Database connection error. Please try again later."]);
            exit(); // Terminate script execution
        }
    }

    /**
     * Starts a new database transaction.
     * @return bool True on success, false on failure.
     */
    public function beginTransaction(): bool {
        return $this->connection->beginTransaction();
    }

    /**
     * Commits the current database transaction.
     * @return bool True on success, false on failure.
     */
    public function commitTransaction(): bool {
        return $this->connection->commit();
    }

    /**
     * Rolls back the current database transaction.
     * @return bool True on success, false on failure.
     */
    public function rollBack(): bool {
        return $this->connection->rollBack();
    }
}
