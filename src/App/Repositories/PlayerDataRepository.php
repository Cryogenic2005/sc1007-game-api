<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/** Repository for account data */
class PlayerDataRepository
{
    private const TABLES = [
        "LEVEL_1" => 'player_data_level_1',
        // "LEVEL_2" => 'player_data_level_2',
        // "LEVEL_3" => 'player_data_level_3',
        // "LEVEL_4" => 'player_data_level_4',
        // "LEVEL_5" => 'player_data_level_5',
        // "LEVEL_6" => 'player_data_level_6'
    ];

    public function __construct(private PDO $pdo) {}

    public function getById(int $id): array
    {
        $data = [];
        foreach (self::TABLES as $key => $table) {
            $stmt = $this->pdo->prepare("SELECT * FROM {$table} WHERE id = :id");
            $stmt->execute(['id' => $id]);

            $playerData = $stmt->fetch(PDO::FETCH_ASSOC);

            $data[$key] = $playerData ?: [];
        }

        return $data;
    }

    public function getAll(): array
    {
        $data = [];
        foreach (self::TABLES as $key => $table) {
            $stmt = $this->pdo->query("SELECT * FROM {$table}");

            $playerData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data[$key] = $playerData;
        }

        return $data;
    }
}