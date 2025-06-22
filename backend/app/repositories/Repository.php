<?php
// app/repositories/Repository.php

namespace App\Repositories;

use \PDO;
use \PDOException;

require_once dirname(__FILE__) . '/../../dbconfig.php'; // Corrected path to dbconfig.php

class Repository {
    protected $connection;
    public function __construct(PDO $existingConnection = null) {
        if ($existingConnection) {
            $this->connection = $existingConnection;
            return;
        }

        // Use global constants defined in dbconfig.php
        global $dsn, $pdoOptions; // Access the global variables from dbconfig.php

        try {
            // Establish a new PDO connection
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $pdoOptions);
            // Set attributes for error mode and default fetch mode
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            error_log("Database connection established successfully.");
        } catch (PDOException $e) {
            // Log the connection error and terminate script execution for critical failures
            error_log("Database Connection Error: " . $e->getMessage());
            // In a production environment, you would want a more graceful error page/response
            http_response_code(500);
            echo json_encode(["message" => "Database connection failed: " . $e->getMessage()]);
            exit();
        }
    }

    public function getConnection(): PDO {
        return $this->connection;
    }
}
