<?php

use App\Database;
use App\Repositories\UserRepository;
use App\Repositories\TokenRepository;
use Slim\Views\PhpRenderer;

return [
    Database::class => function() {
        return new Database($_ENV["DB_HOST"],
                            $_ENV["DB_NAME"]);
    },

    UserRepository::class => function($container) {
        $pdo = $container->get(Database::class)
                         ->getPDO($_ENV["ACCOUNT_INFO_USER"], $_ENV["ACCOUNT_INFO_PASSWORD"]);
        return new UserRepository($pdo);
    },

    TokenRepository::class => function($container) {
        $pdo = $container->get(Database::class)
                         ->getPDO($_ENV["TOKEN_MANAGER_USER"], $_ENV["TOKEN_MANAGER_PASSWORD"]);
        return new TokenRepository($pdo, $container->get(UserRepository::class));
    },
    
    PhpRenderer::class => function($container) {
        return new PhpRenderer(__DIR__ . '/../views');
    }
];