<?php

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;

use App\Controllers\Account;            // Controller for managing actions on accounts
use App\Controllers\ApiRoot;            // Controller for the root of the API
use App\Controllers\CodeSubmission;     // Controller for submitting code to be executed
use App\Controllers\Login;              // Controller for login
use App\Controllers\PlayerData;         // Controller for managing player data
use App\Controllers\TokenIssuer;        // Controller for managing JSON Web Token

use App\Middleware\AddJsonResponseHeader;   // Middleware for adding JSON response header to responses
use App\Middleware\ValidateId;              // Middleware for validating an ID
use App\Middleware\ValidateJWT;             // Middleware for validating a JWT

// Route for the root of the API
$app->group('/api', function (RouteCollectorProxy $group) {

    $group->get('', ApiRoot::class); // Display a welcome message (Use this route to test if the API is working)

    $group->post('/login', Login::class); // Login to the API

    $group->post('/token', TokenIssuer::class); // Retrieve a new JWT

    $group->group('', function (RouteCollectorProxy $group) {
    
        // Route for submitting code to be executed
        $group->post('/code', CodeSubmission::class);

        // Account routes
        $group->group('/account', function (RouteCollectorProxy $group){
    
            $group->get('', [Account::class, 'getAll']); // Get all accounts
    
            $group->post('', [Account::class, 'create']); // Create new account
    
            // Actions that require a specific account ID
            $group->group('/{id:[0-9+]}', function (RouteCollectorProxy $group){
    
                $group->get('', [Account::class, 'getById']); // Get account info
    
                $group->patch('', [Account::class, 'updatePassword']); // Update account password
    
            })->add(ValidateId::class); // Validate the account ID
    
        });

        $group->group('/playerdata', function (RouteCollectorProxy $group){

            $group->get('/{id:[0-9]+}', [PlayerData::class, 'getAllData']);

            $group->get('/{id:[0-9]+}/{name}', [PlayerData::class, 'getPuzzleData']);
    
            $group->patch('', [PlayerData::class, 'updateData']);
    
        });

    })->add(ValidateJWT::class); // Require a valid JWToken for all routes in this group

})->add(AddJsonResponseHeader::class);  // Add JSON response header to API responses