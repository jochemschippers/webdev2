<?php
// app/utils/Response.php

class Response {

    /**
     * Sends a JSON response with data and an HTTP status code.
     *
     * @param mixed $data The data to be sent in the JSON response.
     * @param int $statusCode The HTTP status code (default: 200 OK).
     */
    public static function json($data, $statusCode = 200) {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code($statusCode);
        echo json_encode($data);
        exit(); // Terminate script after sending response
    }

    /**
     * Sends a JSON error response with a message and an HTTP status code.
     *
     * @param string $message The error message.
     * @param int $statusCode The HTTP status code (default: 400 Bad Request).
     */
    public static function error($message, $statusCode = 400) {
        self::json(["message" => $message], $statusCode);
    }
}
?>