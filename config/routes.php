<?php

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;
use \App\Controllers\ApiRoot;
use App\Controllers\Account;
use App\Controllers\Login;
use App\Controllers\Home;
use App\Middleware\ValidateId;
use App\Middleware\AddJsonResponseHeader;
use App\Middleware\ValidateJWT;

// Route to home page
$app->get('/', Home::class);

$app->post('/login', Login::class)
    ->add(AddJsonResponseHeader::class); // Login

// Route for the root of the API
$app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('', ApiRoot::class); // Display a welcome message

    // Account routes
    $group->group('/account', function (RouteCollectorProxy $group){
        // General account actions
        $group->get('', [Account::class, 'getAll']); // Get all accounts
        $group->post('', [Account::class, 'create']); // Create a new account

        // Actions that require a specific account ID
        $group->group('/{id:[0-9+]}', function (RouteCollectorProxy $group){
            $group->get('', [Account::class, 'getById']); // Get target account info
            $group->patch('', [Account::class, 'updatePassword']); // Update target account password
        })->add(ValidateId::class); // Validate the account ID
    });
})->add(AddJsonResponseHeader::class) // Add JSON response header to all routes in the group
  ->add(ValidateJWT::class); // Require an API key for all routes in the group