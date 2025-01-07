<?php

use App\Database;
use App\Repositories\UserRepository;
use App\Repositories\TokenRepository;
use Slim\Views\PhpRenderer;

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
    
    PhpRenderer::class => function() {
        return new PhpRenderer(__DIR__ . '/../views');
    }
];