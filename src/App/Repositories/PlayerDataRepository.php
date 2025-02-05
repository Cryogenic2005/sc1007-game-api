<?php

declare(strict_types=1);

namespace App\Repositories;

/** Repository for managing player data */
class PlayerDataRepository
{
    private const TABLE = 'player_data';

    public function __construct(private \PDO $pdo) {}

    public function getRecord(int $userId, string $puzzle_name = null): array
    {
        $query = "SELECT `puzzle_name`, `time`, `attempts`, `solved` FROM " . self::TABLE . " WHERE user_id = :user_id";

        if ($puzzle_name) {
            $query .= " AND puzzle_name = :puzzle_name";
        }

        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);

        if ($puzzle_name) {
            $stmt->bindParam(':puzzle_name', $puzzle_name, \PDO::PARAM_STR);
        }

        $stmt->execute();

        if ($stmt->rowCount() === 0) { return []; }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function hasRecord(int $userId, string $puzzle_name): bool
    {
        $query = "SELECT COUNT(*) FROM " . self::TABLE . " WHERE user_id = :user_id AND puzzle_name = :puzzle_name";
        $stmt = $this->pdo->prepare($query);
        
        $stmt->execute(['user_id' => $userId, 'puzzle_name' => $puzzle_name]);
        return $stmt->fetchColumn() > 0;
    }

    public function createRecord(int $userId, string $puzzle_name): void
    {
        $query = "INSERT INTO " . self::TABLE . " (user_id, puzzle_name) VALUES (:user_id, :puzzle_name)";
        $stmt = $this->pdo->prepare($query);

        $stmt->execute(['user_id' => $userId, 'puzzle_name' => $puzzle_name]);
    }
    
    public function updateRecordTime(int $userId, string $puzzle_name, int $time): void
    {
        $query = "UPDATE " . self::TABLE . " SET time = :time WHERE user_id = :user_id AND puzzle_name = :puzzle_name";
        $stmt = $this->pdo->prepare($query);

        $stmt->execute(['time' => $time, 'user_id' => $userId, 'puzzle_name' => $puzzle_name]);
    }

    public function updateRecordAttempts(int $userId, string $puzzle_name, int $attempts): void
    {
        $query = "UPDATE " . self::TABLE . " SET attempts = :attempts WHERE user_id = :user_id AND puzzle_name = :puzzle_name";
        $stmt = $this->pdo->prepare($query);

        $stmt->execute(['attempts' => $attempts, 'user_id' => $userId, 'puzzle_name' => $puzzle_name]);
    }

    public function updateRecordSolved(int $userId, string $puzzle_name, bool $solved): void
    {
        $query = "UPDATE " . self::TABLE . " SET solved = :solved WHERE user_id = :user_id AND puzzle_name = :puzzle_name";
        $stmt = $this->pdo->prepare($query);
        
        $stmt->bindParam(':solved', $solved, \PDO::PARAM_BOOL);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':puzzle_name', $puzzle_name, \PDO::PARAM_STR);

        $stmt->execute();
    }
}