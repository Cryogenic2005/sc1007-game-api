<?php

use App\Database;
use App\Repositories\UserRepository;
use App\Repositories\PlayerDataRepository;
use Slim\Views\PhpRenderer;

return [
    Database::class => function() {
        return new Database($_ENV["DB_HOST"],
                            $_ENV["DB_NAME"]);
    },

    UserRepository::class => function($container) {
        $pdo = $container->get(Database::class)
                         ->getPDO($_ENV["ROOT_API_USER"], $_ENV["ROOT_API_PASSWORD"]);
        return new UserRepository($pdo);
    },

    PlayerDataRepository::class => function($container) {
        $pdo = $container->get(Database::class)
                         ->getPDO($_ENV["ROOT_API_USER"], $_ENV["ROOT_API_PASSWORD"]);
        return new PlayerDataRepository($pdo);
    },
    
    PhpRenderer::class => function($container) {
        return new PhpRenderer(__DIR__ . '/../views');
    }
];