<?php

declare(strict_types=1);

namespace App;

use PDO;

class Database
{
    private string $dsn;

    public function __construct(string $host, string $dbname)
    {
        $this->dsn = "mysql:host=$host;dbname=$dbname";
    }

    public function getPDO(string $username, string $password): PDO { 
        return new PDO($this->dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION // Enable exceptions
        ]);
    }
}