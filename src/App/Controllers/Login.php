<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\TokenRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Valitron\Validator;
use App\Repositories\UserRepository;

class Login
{
    private Validator $loginValidator;

    public function __construct(private UserRepository $userRepository, private TokenRepository $tokenRepository) {        
        $this->loginValidator = new Validator();
        $this->loginValidator->mapFieldsRules([
            'username' => ['required'],
            'password' => ['required']
        ]);
    }

    public function __invoke(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        $validator = $this->loginValidator->withData($body);
        // Validate the request body
        if(!$validator->validate()){ // If validation fails
            $response->getBody()
                     ->write(json_encode($validator->errors(), 
                                         JSON_FORCE_OBJECT));

            return $response->withStatus(400);
        }

        $username = $body['username'];
        $password = $body['password'];
        
        $account = $this->userRepository->getByUsername($username);

        // If account is not found or password is incorrect, return a 401 Unauthorized response
        if ($account === null || !password_verify($password, $account['password'])) {
            $body = json_encode([
                'status' => 'error',
                'message' => 'Invalid username or password'
            ], JSON_FORCE_OBJECT);

            $response->getBody()->write($body);

            return $response->withStatus(401);
        }

        $generatedTokenInfo = $this->tokenRepository->createRefreshToken($account['id'], $account['username']);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'Account logged in',
            'id' => $generatedTokenInfo['payload']['sub'],
            'exp' => $generatedTokenInfo['payload']['exp'],
            'token' => $generatedTokenInfo['token'],
        ], JSON_FORCE_OBJECT));

        return $response;
    }
}