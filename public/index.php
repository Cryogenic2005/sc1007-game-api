<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\Account;
use App\Controllers\PlayerData;
use App\Middleware\AddJsonResponseHeader;
use App\Middleware\ValidateId;

define('APP_ROOT', dirname(__DIR__));

// Load the Composer autoloader
require_once APP_ROOT . '\vendor\autoload.php';

// Load the environment variables
$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

// Build the container
$containerBuilder = new ContainerBuilder();

// Add container definitions
$container = $containerBuilder->addDefinitions(APP_ROOT . '\config\definitions.php')
                                ->build();

AppFactory::setContainer($container);

// Create a new Slim app
$app = AppFactory::create();

// Set the default invocation strategy
$app->getRouteCollector()->setDefaultInvocationStrategy(new RequestResponseArgs());

// Add middleware
$app->add(new AddJsonResponseHeader()); // Add JSON response header to all responses
$app->addBodyParsingMiddleware(); // Add body parsing middleware
$error_middleware = $app->addErrorMiddleware(displayErrorDetails: true,
                                             logErrors: true, 
                                             logErrorDetails: true); // Add error handling middleware


// Configure the default error handler to return JSON
$error_handler = $error_middleware->getDefaultErrorHandler();
$error_handler->forceContentType('application/json'); // Force JSON error responses

// Account routes
$app->group('/account', function (RouteCollectorProxy $group){
    
    $group->get('', [Account::class, 'getAll']);

    $group->post('', [Account::class, 'create']);

    $group->put('/login', [Account::class, 'login']);

    $group->group('/{id:[0-9+]}', function (RouteCollectorProxy $group){
        
        $group->get('', [Account::class, 'getById']);

        $group->patch('', [Account::class, 'updatePassword']);

    })->add(ValidateId::class);

});

// Player data routes
$app->group('/player-data', function (RouteCollectorProxy $group){
    $group->get('', [PlayerData::class, 'getAll']);
    
    $group->group('/{id:[0-9]+}', function (RouteCollectorProxy $group){
        
        $group->get('', [PlayerData::class, 'getById']);

        $group->patch('', [PlayerData::class, 'updateData']);

    })->add(ValidateId::class);;

});

// Run the Slim app
$app->run();