<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Authorization\JWTHelper;
use Valitron\Validator;
use App\Repositories\UserRepository;

class Login
{
    private Validator $loginValidator;

    public function __construct(private UserRepository $userRepository) {        
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
                'message' => 'Invalid username or password',
                'id' => null
            ], JSON_FORCE_OBJECT);

            $response->getBody()->write($body);

            return $response->withStatus(401);
        }

        $token = JWTHelper::createJWT([
            'id' => $account['id'],
            'name' => $account['username']
        ]);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'Account logged in',
            'token' => $token
        ], JSON_FORCE_OBJECT));

        return $response;
    }
}