<?php
// app/utils/JWTUtility.php

namespace App\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key; // For decoding JWTs

class JWTUtility {
    private static $secretKey;
    private static $alg = 'HS256'; // Hashing algorithm
    private function __construct() {
        // Load environment variables if .env is used
        // Make sure you have `composer require vlucas/phpdotenv` and a .env file
        // Or ensure the environment variable is set via docker-compose.yml
        if (file_exists(dirname(__DIR__, 2) . '/.env')) {
            $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
            $dotenv->load();
        }

        self::$secretKey = getenv('JWT_SECRET') ?: 'your_default_super_secret_key_if_env_not_set';
        if (self::$secretKey === 'your_default_super_secret_key_if_env_not_set') {
            error_log("WARNING: JWT_SECRET environment variable is not set. Using a default, insecure key. This is NOT recommended for production!");
        }
    }

    /**
     * Initializes the secret key if not already set.
     * This method ensures the secret key is loaded, preferably from environment variables.
     */
    private static function initializeSecretKey() {
        if (self::$secretKey === null) {
            new self(); // Call the private constructor to load the secret key
        }
    }

    /**
     * Generates a JSON Web Token.
     *
     * @param array $data The payload data to be encoded in the JWT (e.g., user ID, username, role).
     * @param int $expirationMinutes The number of minutes until the token expires.
     * @return string The encoded JWT.
     */
    public static function generateToken(array $data, int $expirationMinutes = 60): string {
        self::initializeSecretKey(); // Ensure secret key is loaded

        $issuedAt = time();
        $expirationTime = $issuedAt + ($expirationMinutes * 60); // Token valid for $expirationMinutes

        $payload = [
            'iat'  => $issuedAt,          // Issued at: timestamp when the token was issued
            'exp'  => $expirationTime,    // Expiration time: timestamp when the token will expire
            'data' => $data               // Custom data, e.g., user information
        ];

        try {
            $jwt = JWT::encode($payload, self::$secretKey, self::$alg);
            error_log("JWTUtility: Token generated successfully for user data: " . json_encode($data));
            return $jwt;
        } catch (\Exception $e) {
            error_log("JWTUtility: Error generating token: " . $e->getMessage());
            // In a real application, you might throw a custom exception or handle this more gracefully.
            return ''; // Return empty string on failure
        }
    }

    /**
     * Decodes and validates a JSON Web Token.
     *
     * @param string $jwt The encoded JWT.
     * @return object|false The decoded payload data if valid, false otherwise.
     */
    public static function decodeToken(string $jwt): object|false {
        self::initializeSecretKey(); // Ensure secret key is loaded

        try {
            // Decoding the token. The `Key` object is used to specify the secret and algorithm.
            $decoded = JWT::decode($jwt, new Key(self::$secretKey, self::$alg));
            error_log("JWTUtility: Token decoded successfully.");
            return $decoded->data; // Return the custom 'data' part of the payload
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            error_log("JWTUtility: Invalid signature: " . $e->getMessage());
            return false;
        } catch (\Firebase\JWT\BeforeValidException $e) {
            error_log("JWTUtility: Token not yet valid: " . $e->getMessage());
            return false;
        } catch (\Firebase\JWT\ExpiredException $e) {
            error_log("JWTUtility: Token expired: " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log("JWTUtility: Error decoding token: " . $e->getMessage());
            return false;
        }
    }
}
