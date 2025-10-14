<?php
namespace Src\Controllers;

use Src\Core\Database;
use Src\Helpers\ResponseHelper;
use Src\Repositories\UserRepository;
use Src\Services\AuthService;

/**
 * Class AuthController
 *
 * Handles user authentication actions such as registration and login.
 * Validates request payloads, delegates business logic to the AuthService,
 * and returns appropriate JSON responses.
 *
 * @package Src\Controllers
 */
class AuthController
{
    /**
     * Authentication service instance.
     *
     * @var AuthService
     */
    private AuthService $authService;

    /**
     * AuthController constructor.
     *
     * Initializes dependencies required for authentication.
     * Loads configuration, establishes a database connection,
     * and creates a UserRepository and AuthService instance.
     */
    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $db = new Database($config['db']);
        $userRepository = new UserRepository($db);
        $this->authService = new AuthService($userRepository);
    }

    /**
     * Register a new user.
     *
     * Expects a JSON request body with:
     * - `name` (string)
     * - `email` (string)
     * - `password` (string)
     * @throws \InvalidArgumentException If any required field is missing or invalid.
     * @throws \RuntimeException If user already exists or database issue occurs.
     * @throws \Throwable For unexpected internal errors.
     */
    public function register(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        try {
            if (!isset($data['name'], $data['email'], $data['password'])) {
                ResponseHelper::json(['error' => 'name, email and password required'], 422);
            }

            $user = $this->authService->register(
                $data['name'],
                $data['email'],
                $data['password']
            );

            ResponseHelper::json([
                'success' => 'User registered',
                'user' => $user,
            ], 201);

        } catch (\InvalidArgumentException $e) {
            ResponseHelper::json(['error' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            ResponseHelper::json(['error' => $e->getMessage()], 409);
        } catch (\Throwable $e) {
            ResponseHelper::json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Log in an existing user.
     *
     * Expects a JSON request body with:
     * - `email` (string)
     * - `password` (string)
     * On success, returns a JWT token and user info.
     * @throws \InvalidArgumentException If required fields are missing or invalid.
     * @throws \RuntimeException If authentication fails.
     * @throws \Throwable For unexpected internal errors.
     */
    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        try {
            if (!isset($data['email'], $data['password'])) {
                ResponseHelper::json(['error' => 'email and password required for login'], 422);
            }

            $user = $this->authService->login(
                $data['email'],
                $data['password']
            );

            ResponseHelper::json([
                'success' => 'Login successful',
                'user' => $user,
            ], 200);

        } catch (\InvalidArgumentException $e) {
            ResponseHelper::json(['error' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            ResponseHelper::json(['error' => $e->getMessage()], 409);
        } catch (\Throwable $e) {
            ResponseHelper::json(['error' => $e->getMessage()], 500);
        }
    }
}
