<?php

declare(strict_types=1);

namespace App\Repositories;

/** Repository for managing player data */
class PlayerDataRepository
{
    private const TABLE = 'player_data';

    public function __construct(private \PDO $pdo) {}

    public function getRecord(int $playerId, string $puzzle_name): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM " . self::TABLE . " WHERE player_id = :player_id AND puzzle_name = :puzzle_name");
        $stmt->execute(['player_id' => $playerId, 'puzzle_name' => $puzzle_name]);
        return $stmt->fetch();
    }

    public function hasRecord(int $playerId, string $puzzle_name): bool
    {
        $query = "SELECT COUNT(*) FROM " . self::TABLE . " WHERE player_id = :player_id AND puzzle_name = :puzzle_name";
        $stmt = $this->pdo->prepare($query);
        
        $stmt->execute(['player_id' => $playerId, 'puzzle_name' => $puzzle_name]);
        return $stmt->fetchColumn() > 0;
    }

    public function createRecord(int $playerId, string $puzzle_name): void
    {
        $query = "INSERT INTO " . self::TABLE . " (player_id, puzzle_name) VALUES (:player_id, :puzzle_name)";
        $stmt = $this->pdo->prepare($query);

        $stmt->execute(['player_id' => $playerId, 'puzzle_name' => $puzzle_name]);
    }
    
    public function updateRecordTime(int $playerId, string $puzzle_name, int $time): void
    {
        $query = "UPDATE " . self::TABLE . " SET time = :time WHERE player_id = :player_id AND puzzle_name = :puzzle_name";
        $stmt = $this->pdo->prepare($query);

        $stmt->execute(['time' => $time, 'player_id' => $playerId, 'puzzle_name' => $puzzle_name]);
    }

    public function updateRecordAttempts(int $playerId, string $puzzle_name, int $attempts): void
    {
        $query = "UPDATE " . self::TABLE . " SET attempts = :attempts WHERE player_id = :player_id AND puzzle_name = :puzzle_name";
        $stmt = $this->pdo->prepare($query);

        $stmt->execute(['attempts' => $attempts, 'player_id' => $playerId, 'puzzle_name' => $puzzle_name]);
    }
}