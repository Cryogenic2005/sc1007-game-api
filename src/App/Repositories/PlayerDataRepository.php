<?php

declare(strict_types=1);

namespace App\Repositories;

/** Repository for managing player data */
class PlayerDataRepository
{
    private const TABLE = 'player_data';

    public function __construct(private \PDO $pdo) {}
    
    public function updatePlayerData(int $playerId, string $field, $value): bool
    {
        // Check if the field exists
        $stmt = $this->pdo->prepare("SELECT $field FROM " . self::TABLE . " WHERE player_id = :player_id");
        $stmt->execute(['player_id' => $playerId]);
        if ($stmt->rowCount() === 0) {
            return false; // Field does not exist
        }

        // Update the field with the new value
        $stmt = $this->pdo->prepare("UPDATE " . self::TABLE . " SET $field = :value WHERE player_id = :player_id");
        $stmt->execute(['player_id' => $playerId, 'value' => $value]);

        return true; // Success
    }
}