<?php

namespace Src\Repositories;

use PDO;
use Src\Core\Database;

/**
 * Class UserRepository
 *
 * Handles database operations related to users such as creating accounts
 * and retrieving user records by ID or email.
 */
class UserRepository
{
    /**
     * @var PDO Database connection instance
     */
    private PDO $db;

    /**
     * UserRepository constructor.
     *
     * @param Database $database 
     */
    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    /**
     * Create a new user in the database.
     *
     * @param string $name          User's full name
     * @param string $email         User's email address (must be unique)
     * @param string $passwordHash  Hashed password value
     *
     * @return int Newly created user's ID
     */
    public function createUser(string $name, string $email, string $passwordHash): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password_hash)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$name, $email, $passwordHash]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Find a user record by their email address.
     *
     * @param string $email User's email address
     *
     * @return array|null User data as associative array, or null if not found
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Find a user record by their unique ID.
     *
     * @param int $id User ID
     *
     * @return array|null User data (id, name, email, created_at) or null if not found
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, email, created_at
            FROM users
            WHERE id = ?
        ");
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}
