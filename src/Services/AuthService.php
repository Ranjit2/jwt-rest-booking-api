<?php 
namespace Src\Services;

use Src\Repositories\UserRepository;
use Firebase\JWT\JWT;

/**
 * Class AuthService
 *
 * Handles user registration and authentication (login + JWT generation).
 */
class AuthService {
    
    /**
     * @var UserRepository Repository instance for user data operations
     */
    private UserRepository $userRepository;

    /**
     * @var string Secret key used for JWT encoding
     */
    private string $key;

    /**
     * AuthService constructor.
     *
     * Loads JWT secret key from configuration and sets the repository dependency.
     *
     * @param UserRepository $userRepository 
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;

        // Load config file containing JWT secret
        $config = require __DIR__ . '/../../config/config.php';
        $this->key = $config['jwt_secret'];
    }

    public function register(string $name, string $email, string $password): array
    {
        if (empty($name) || empty($email) || empty($password)) {
            throw new \InvalidArgumentException("Name, Email, and Password are required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format");
        }

        if (strlen($password) < 6) {
            throw new \InvalidArgumentException('Password must be at least 6 characters.');
        }

        // check duplicates
        if ($this->userRepository->findByEmail($email)) {
            throw new \RuntimeException('Email already registered.');
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $userId = $this->userRepository->createUser($name, $email, $passwordHash);

        return [
            'user_id' => $userId,
            'name' => $name,
            'email' => $email
        ];

    }
    public function login($email, $password): array 
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new \Exception('Invalid email or password');
        }

        $payload = [
            'sub' => $user['id'],
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + (60*60*24)
        ];
        $token = JWT::encode($payload, $this->key,  $alg = 'HS256');

  
        return [
            'token' => $token
        ];

    }
}