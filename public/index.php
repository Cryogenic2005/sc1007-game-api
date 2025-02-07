<?php

declare(strict_types=1);

use App\AppBuilder;
use Dotenv\Dotenv;

define('APP_ROOT', dirname(__DIR__));

// Load the Composer autoloader
require_once APP_ROOT . '/vendor/autoload.php';

// Load the environment variables
if (file_exists(APP_ROOT . '/.env')) {
    $dotenv = Dotenv::createImmutable(APP_ROOT);
    $dotenv->load();
}

$app = new AppBuilder();
$app->get()->run();