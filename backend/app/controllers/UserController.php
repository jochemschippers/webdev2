<?php
// app/controllers/UserController.php

namespace App\Controllers;

require_once __DIR__ . '/Controller.php';
require_once dirname(__FILE__) . '/../services/UserService.php';

use App\Services\UserService;

class UserController extends Controller
{
    private $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = new UserService();
    }

    public function register()
    {
        $data = $this->getJsonInput();

        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            $this->errorResponse("Missing required fields: username, email, and password are required.", 400);
        }

        $username = $data['username'];
        $email = $data['email'];
        $password = $data['password'];

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
            ], 201);
        } else {
            $this->errorResponse("User registration failed. Username or email might already be taken.", 409);
        }
    }

    public function login()
    {
        $data = $this->getJsonInput();

        if (empty($data['username']) && empty($data['email'])) {
            $this->errorResponse("Username or email is required.", 400);
        }
        if (empty($data['password'])) {
            $this->errorResponse("Password is required.", 400);
        }

        $identifier = $data['username'] ?? $data['email'];
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
                "token" => $result['token']
            ], 200);
        } else {
            $this->errorResponse("Login failed. Invalid credentials.", 401);
        }
    }

    public function profile(int $userId) {
        $userProfile = $this->userService->getUserProfile($userId);

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
            $this->errorResponse("User profile not found for ID: {$userId}.", 404);
        }
    }
}
