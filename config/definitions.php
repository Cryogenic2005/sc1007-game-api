<?php

use App\Database;
use App\Repositories\UserRepository;
use App\Repositories\TokenRepository;
use App\Repositories\PlayerDataRepository;

return [
    Database::class => function() {
        return new Database($_SERVER["DB_HOST"],
                            $_SERVER["DB_NAME"]);
    },

    UserRepository::class => function($container) {
        $pdo = $container->get(Database::class)
                         ->getPDO($_SERVER["ACCOUNT_INFO_USER"], $_SERVER["ACCOUNT_INFO_PASSWORD"]);
        return new UserRepository($pdo);
    },

    TokenRepository::class => function($container) {
        $pdo = $container->get(Database::class)
                         ->getPDO($_SERVER["TOKEN_MANAGER_USER"], $_SERVER["TOKEN_MANAGER_PASSWORD"]);
        return new TokenRepository($pdo);
    },

    PlayerDataRepository::class => function($container) {
        $pdo = $container->get(Database::class)
                         ->getPDO($_SERVER["PLAYER_DATA_USER"], $_SERVER["PLAYER_DATA_PASSWORD"]);
        return new PlayerDataRepository($pdo);
    }
];