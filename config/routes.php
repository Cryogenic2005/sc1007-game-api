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

return function ($app) {
    // Route for the root of the API
    $app->group('/api', function (RouteCollectorProxy $group) {

        // Display a welcome message (Used for testing if API works)
        $group->get('', ApiRoot::class); 
    
        $group->post('/login', Login::class); 
    
        $group->post('/token', TokenIssuer::class); // Retrieve a new JWT
    
        // Group for routes that require a valid JWT
        $group->group('', function (RouteCollectorProxy $group) {
        
            // Route for submitting code to be executed
            $group->post('/code', CodeSubmission::class);
    
            // Operations on account data
            $group->group('/account', function (RouteCollectorProxy $group){
        
                $group->get('', [Account::class, 'getAll']);
        
                $group->post('', [Account::class, 'create']);
        
                // Operations on a specific account
                $group->group('/{id:[0-9+]}', function (RouteCollectorProxy $group){
        
                    $group->get('', [Account::class, 'getById']);
        
                    $group->patch('', [Account::class, 'updatePassword']);
        
                })->add(ValidateId::class); // Validate the requested account ID
        
            });
    
            // Operations on player data
            $group->group('/playerdata', function (RouteCollectorProxy $group){
    
                // Operations on player data of a specific player
                $group->group('/{id:[0-9]+}', function (RouteCollectorProxy $group) {
        
                    $group->get('', [PlayerData::class, 'getAllData']);
        
                    $group->patch('', [PlayerData::class, 'updateData']);
                    
                    $group->get('/{name}', [PlayerData::class, 'getPuzzleData']);
                
                })->add(ValidateId::class); // Validate the requested player ID
        
            });
    
        })->add(ValidateJWT::class);
    
    })->add(AddJsonResponseHeader::class);  // Add JSON response header to API responses
};