<?php
// app/controllers/Controller.php

namespace App\Controllers;

require_once dirname(__FILE__) . '/../utils/Response.php';

use App\Utils\Response;

class Controller
{
    public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8"); // Default content type for responses
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    protected function jsonResponse($data, $statusCode = 200)
    {
        Response::json($data, $statusCode);
    }

    protected function errorResponse($message, $statusCode = 400)
    {
        Response::error($message, $statusCode);
    }

    protected function getJsonInput() {
        $input = file_get_contents("php://input");
        error_log("Controller: Raw JSON input received: " . ($input ?: "EMPTY")); // DEBUG: Log raw input

        if (!empty($input)) {
            $data = json_decode($input, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                error_log("Controller: Successfully decoded JSON data: " . print_r($data, true)); // DEBUG: Log decoded data
                return $data;
            } else {
                // Log JSON decoding error
                error_log("Controller: JSON decoding error: " . json_last_error_msg() . " (Error Code: " . json_last_error() . ")"); // DEBUG
                error_log("Controller: Corrupted input was: " . $input); // DEBUG
                return null;
            }
        }
        error_log("Controller: No input found or input was empty."); // DEBUG
        return null;
    }
}
