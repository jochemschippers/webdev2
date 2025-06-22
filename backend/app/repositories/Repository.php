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
        error_log("Repository: __construct started. Attempting to load dbconfig.php"); // DEBUG
        require_once __DIR__ . '/../../dbconfig.php';
        error_log("Repository: dbconfig.php loaded successfully."); // DEBUG

        $type = 'mysql'; // Database type
        $servername = \DB_HOST;
        $database = \DB_NAME;
        $username = \DB_USER;
        $password = \DB_PASS;

        error_log("Repository: DB connection parameters: Host=" . $servername . ", DB=" . $database . ", User=" . $username); // DEBUG

        try {
            error_log("Repository: Attempting to establish PDO connection..."); // DEBUG
            $this->connection = new PDO(
                "$type:host=$servername;dbname=$database",
                $username,
                $password,
                [
                    // Set a timeout for the connection and query execution
                    PDO::ATTR_TIMEOUT => 5, // 5 seconds
                ]
            );
            error_log("Repository: PDO connection established successfully with timeout of 5 seconds."); // DEBUG

            // Set PDO attributes for error handling and fetching
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // For real prepared statements
            $this->connection->exec("SET NAMES utf8mb4"); // Ensure UTF-8 support
            error_log("Repository: PDO attributes set and charset configured."); // DEBUG

        } catch (PDOException $e) {
            error_log("Repository: Database connection FAILED with PDOException: " . $e->getMessage()); // DEBUG
            $errorInfo = $e->errorInfo;
            if ($errorInfo) {
                error_log("Repository: PDO Error Code: " . $errorInfo[0] . ", Driver Error Code: " . $errorInfo[1] . ", Driver Error Message: " . $errorInfo[2]); // DEBUG
            }
            if (!headers_sent()) {
                http_response_code(500);
                echo json_encode(["message" => "Database connection error. Please try again later."]);
            }
            exit();
        } catch (\Exception $e) {
            error_log("Repository: General Exception during database connection: " . $e->getMessage()); // DEBUG
            if (!headers_sent()) {
                http_response_code(500);
                echo json_encode(["message" => "An unexpected error occurred during database connection."]);
            }
            exit();
        }
    }

    /**
     * Starts a new database transaction.
     * @return bool True on success, false on failure.
     */
    public function beginTransaction(): bool
    {
        error_log("Repository: Attempting to beginTransaction(). Current PDO state: " . ($this->connection->inTransaction() ? "active" : "inactive")); // DEBUG
        try {
            $result = $this->connection->beginTransaction();
            error_log("Repository: beginTransaction() result: " . ($result ? "true" : "false") . ". New PDO state: " . ($this->connection->inTransaction() ? "active" : "inactive")); // DEBUG
            return $result;
        } catch (PDOException $e) {
            error_log("Repository: PDOException during beginTransaction: " . $e->getMessage()); // DEBUG
            return false;
        }
    }

    /**
     * Commits the current database transaction.
     * @return bool True on success, false on failure.
     */
    public function commitTransaction(): bool
    {
        error_log("Repository: Attempting to commitTransaction(). Current PDO state: " . ($this->connection->inTransaction() ? "active" : "inactive")); // DEBUG
        try {
            if ($this->connection->inTransaction()) {
                $result = $this->connection->commit();
                error_log("Repository: commitTransaction() result: " . ($result ? "true" : "false") . ". New PDO state: " . ($this->connection->inTransaction() ? "active" : "inactive")); // DEBUG
                return $result;
            }
            error_log("Repository: No active transaction to commit."); // DEBUG
            return false;
        } catch (PDOException $e) {
            error_log("Repository: PDOException during commitTransaction: " . $e->getMessage()); // DEBUG
            return false;
        }
    }

    /**
     * Rolls back the current database transaction.
     * @return bool True on success, false on failure.
     */
    public function rollBack(): bool
    {
        error_log("Repository: Attempting to rollBack(). Current PDO state: " . ($this->connection->inTransaction() ? "active" : "inactive")); // DEBUG
        try {
            if ($this->connection->inTransaction()) {
                $result = $this->connection->rollBack();
                error_log("Repository: rollBack() result: " . ($result ? "true" : "false") . ". New PDO state: " . ($this->connection->inTransaction() ? "active" : "inactive")); // DEBUG
                return $result;
            }
            error_log("Repository: No active transaction to roll back."); // DEBUG
            return false;
        } catch (PDOException $e) {
            error_log("Repository: PDOException during rollBack: " . $e->getMessage()); // DEBUG
            return false;
        }
    }

    /**
     * Checks if a transaction is currently active.
     * @return bool True if a transaction is active, false otherwise.
     */
    public function inTransaction(): bool
    {
        error_log("Repository: Calling inTransaction(). Current PDO state: " . ($this->connection->inTransaction() ? "active" : "inactive")); // DEBUG
        return $this->connection->inTransaction();
    }
}
