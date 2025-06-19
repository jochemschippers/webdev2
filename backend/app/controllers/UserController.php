<?php
// app/controllers/UserController.php

namespace App\Controllers; // Use the same namespace as the base Controller

require_once __DIR__ . '/Controller.php'; // Require the base Controller
require_once dirname(__FILE__) . '/../services/UserService.php'; // Require the UserService

use App\Services\UserService; // Use the UserService from the App\Services namespace

class UserController extends Controller
{
    private $userService;

    /**
     * Constructor for UserController.
     * Initializes the UserService.
     */
    public function __construct()
    {
        parent::__construct(); // Call the parent constructor to set up headers
        $this->userService = new UserService(); // Instantiate the UserService
    }

    /**
     * Handles user registration.
     * Expects JSON input with 'username', 'email', 'password'.
     *
     * Route: POST /api/register
     */
    public function register()
    {
        $data = $this->getJsonInput();

        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            $this->errorResponse("Missing required fields: username, email, and password are required.", 400);
        }

        $username = $data['username'];
        $email = $data['email'];
        $password = $data['password']; // This will be hashed in the service layer

        $user = $this->userService->registerUser($username, $email, $password);

        if ($user) {
            $this->jsonResponse([
                "message" => "User registered successfully.",
                "user" => [
                    "id" => $user->id,
                    "username" => $user->username,
                    "email" => $user->email,
                    "role" => $user->role
                ]
            ], 201); // 201 Created
        } else {
            $this->errorResponse("User registration failed. Username or email might already be taken.", 409); // 409 Conflict
        }
    }

    /**
     * Handles user login.
     * Expects JSON input with 'username' or 'email', and 'password'.
     *
     * Route: POST /api/login
     */
    public function login()
    {
        $data = $this->getJsonInput();

        if (empty($data['username']) && empty($data['email'])) {
            $this->errorResponse("Username or email is required.", 400);
        }
        if (empty($data['password'])) {
            $this->errorResponse("Password is required.", 400);
        }

        $identifier = $data['username'] ?? $data['email']; // Can log in with username or email
        $password = $data['password'];

        $result = $this->userService->loginUser($identifier, $password);

        if ($result && isset($result['user']) && isset($result['token'])) {
            $this->jsonResponse([
                "message" => "Login successful.",
                "user" => [
                    "id" => $result['user']->id,
                    "username" => $result['user']->username,
                    "email" => $result['user']->email,
                    "role" => $result['user']->role
                ],
                "token" => $result['token'] // Bearer token for authentication
            ], 200);
        } else {
            $this->errorResponse("Login failed. Invalid credentials.", 401); // 401 Unauthorized
        }
    }

    /**
     * Gets authenticated user's profile.
     * This method assumes token-based authentication is handled by a middleware
     * or directly in the router, which populates the user ID or object.
     * For now, we'll simulate. In a real app, this would be protected.
     *
     * Route: GET /api/user/profile (protected)
     */
    public function profile() {
        // In a real application, the authenticated user ID would be available
        // from a JWT token validation, session, or similar.
        // For demonstration, let's assume user ID 1 is logged in.
        // You would typically get this from an Authorization header or similar.
        // $userId = Auth::getUserId(); // Placeholder for actual auth system

        // For now, let's return the admin user as a placeholder profile
        $userProfile = $this->userService->getUserProfile(1); // Fetch user ID 1 as an example

        if ($userProfile) {
            $this->jsonResponse([
                "message" => "User profile retrieved.",
                "user" => [
                    "id" => $userProfile->id,
                    "username" => $userProfile->username,
                    "email" => $userProfile->email,
                    "role" => $userProfile->role
                ]
            ], 200);
        } else {
            $this->errorResponse("Unauthorized or user profile not found.", 401);
        }
    }
}
?>
