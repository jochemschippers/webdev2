<?php
// app/middlewares/AuthMiddleware.php

namespace App\Middlewares;

require_once dirname(__FILE__) . '/../utils/JWTUtility.php';
require_once dirname(__FILE__) . '/../utils/Response.php'; // For sending error responses

use App\Utils\JWTUtility;
use App\Utils\Response;

class AuthMiddleware {

    /**
     * Authenticates the request using a JWT from the Authorization header.
     *
     * @param array $requiredRoles Optional. An array of roles that are allowed to access this route.
     * If empty, only authentication (valid token) is checked, no specific role.
     * @return object|false Returns the decoded user data (from JWT payload) on successful authentication,
     * or false if authentication fails.
     */
    public static function authenticate(array $requiredRoles = []): object|false {
        // Get the Authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        // Check if the header is present and in "Bearer <token>" format
        if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            Response::error("Authentication token missing or invalid format.", 401);
            // Exit is called within Response::error, but returning false for clarity if called elsewhere
            return false;
        }

        $token = $matches[1]; // Extract the token string

        // Decode and validate the token
        $decodedData = JWTUtility::decodeToken($token);

        if (!$decodedData) {
            // JWTUtility::decodeToken already logs specific errors (expired, invalid signature, etc.)
            Response::error("Invalid or expired authentication token.", 401);
            return false;
        }

        // Token is valid; now check for role if requiredRoles are specified
        if (!empty($requiredRoles)) {
            $userRole = $decodedData->role ?? null; // Get the role from the decoded token's data
            if ($userRole === null || !in_array($userRole, $requiredRoles)) {
                Response::error("Access Denied. Insufficient permissions for this resource.", 403);
                return false;
            }
        }

        // Authentication and authorization successful
        return $decodedData; // Return the decoded user data
    }
}
