<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestResponseArgs;

define('APP_ROOT', dirname(__DIR__));

// Load the Composer autoloader
require_once APP_ROOT . '/vendor/autoload.php';

// Load the environment variables
if (file_exists(APP_ROOT . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
    $dotenv->load();
}

// Build the container
$containerBuilder = new ContainerBuilder();

// Add container definitions
$container = $containerBuilder->addDefinitions(APP_ROOT . '/config/definitions.php')
                                ->build();

AppFactory::setContainer($container);

// Create a new Slim app
$app = AppFactory::create();

// Set the default invocation strategy
$app->getRouteCollector()->setDefaultInvocationStrategy(new RequestResponseArgs());

// Add middleware
$app->addBodyParsingMiddleware(); // Add body parsing middleware
$app->addErrorMiddleware(displayErrorDetails: true,
                         logErrors: true, 
                         logErrorDetails: true); // Add error handling middleware


// Load the routes
require_once APP_ROOT . '/config/routes.php';

// Run the Slim app
$app->run();