<?php

namespace Src\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Src\Helpers\ResponseHelper;

/**
 * Class AuthMiddleware
 *
 * Middleware that validates incoming HTTP requests for JWT authentication.
 * Checks for the presence and validity of the `Authorization: Bearer <token>` header,
 * decodes the token, and returns the authenticated user's payload.
 *
 * Used to protect API routes that require authentication.
 *
 * @package Src\Middleware
 */
class AuthMiddleware
{
    /**
     * Handle authentication middleware.
     *
     * Verifies the presence and structure of the `Authorization` header,
     * decodes the JWT token using the configured secret key,
     * and returns the decoded payload object if valid.
     *
     * On failure, automatically sends a JSON error response
     * and terminates execution.
     *
     * @example
     * Header:
     *   Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
     *
     * @return object Decoded JWT payload containing user data (e.g., `sub`, `email`).
     *
     * @throws \Exception If the token is invalid or expired.
     */
    public static function handle()
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            ResponseHelper::json(['error' => 'Authorization header missing'], 401);
        }

        $authHeader = explode(' ', $headers['Authorization']);
        if (count($authHeader) !== 2 || strtolower($authHeader[0]) !== 'bearer') {
            ResponseHelper::json(['error' => 'Authorization header missing'], 400);
        }

        $token = $authHeader[1];

        $config = require __DIR__ . '/../../config/config.php';
        $secretKey = $config['jwt_secret'];

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            return $decoded;

        } catch (\Exception $e) {
            ResponseHelper::json(['error' => 'Invalid or expired token'], 401);
        }
    }
}
