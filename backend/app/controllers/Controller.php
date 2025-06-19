<?php

namespace App\Controllers; 

require_once dirname(__FILE__) . '/../utils/Response.php'; 

use App\Utils\Response;

class Controller
{
    public function __construct()
    {
        header("Access-Control-Allow-Origin: *"); // Allow requests from any origin (for development)
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allow common HTTP methods
        header("Access-Control-Max-Age: 3600"); // Cache preflight requests for 1 hour
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        // Handle preflight requests (OPTIONS method)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit(); // Terminate script for preflight
        }
    }

    /**
     * Helper to send JSON response.
     * @param mixed $data The data to send.
     * @param int $statusCode HTTP status code.
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        Response::json($data, $statusCode);
    }

    /**
     * Helper to send JSON error response.
     * @param string $message The error message.
     * @param int $statusCode HTTP status code.
     */
    protected function errorResponse($message, $statusCode = 400)
    {
        Response::error($message, $statusCode);
    }

    /**
     * Parses the JSON body from a POST/PUT request.
     * @return array|null Returns decoded JSON data as an associative array, or null if empty/invalid.
     */
    protected function getJsonInput() {
        $input = file_get_contents("php://input");
        if (!empty($input)) {
            $data = json_decode($input, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }
        return null;
    }
}
?>
