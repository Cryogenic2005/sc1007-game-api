<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/** Repository for account data */
class UserRepository
{
    private const TABLE = 'account_info';

    public function __construct(private PDO $pdo) {}

    /**
     * Get account by username
     * @param string $username The account username
     * @return array The account
     */
    public function getByUsername(string $username): array|null
    {
        $stmt = $this->pdo->prepare('SELECT * FROM ' . self::TABLE . ' WHERE username = :username');
        $stmt->execute(['username' => $username]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get account by ID
     * @param int $id The account ID
     * @return array The account
     */
    public function getById(int $id): array|null
    {
        $sql = 'SELECT * FROM ' . self::TABLE . ' WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get all accounts
     * @return array All accounts
     */
    public function getAll(): array
    {
        $sql = 'SELECT * FROM ' . self::TABLE;
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new account
     * @param string $username The account username
     * @param string $password The account password
     * @return int The new account ID
     */
    public function create(string $username, string $password): int
    {
        $password = password_hash($password, PASSWORD_DEFAULT);

        $sql = 'INSERT INTO ' . self::TABLE . ' (username, password) VALUES (:username, :password)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['username' => $username, 'password' => $password]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Update password by ID
     * @param int $id The account ID to update
     * @param string $password The new password
     */
    public function updatePassword(int $id, string $password): void
    {
        $password = password_hash($password, PASSWORD_DEFAULT);

        $sql = 'UPDATE ' . self::TABLE . ' SET password = :password WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id, 'password' => $password]);
    }
}