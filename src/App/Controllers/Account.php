<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Repositories\UserRepository;
use Valitron\Validator;

class Account
{
    private Validator $createValidator;
    private Validator $updatePasswordValidator;

    public function __construct(private UserRepository $userRepository)
    {
        $this->createValidator = new Validator();
        $this->createValidator->mapFieldsRules([
            'username' => ['required'],
            'password' => ['required', ['lengthBetween', 8, 32]]
        ]);

        $this->updatePasswordValidator = new Validator();
        $this->updatePasswordValidator->mapFieldsRules([
            'password' => ['required'],
            'newPassword' => ['required', ['lengthBetween', 8, 32]]
        ]);
    }

    public function getAll(Request $request, Response $response)
    {
        $data = $this->userRepository->getAll();

        // Should not display the password
        foreach ($data as $key => $value) {
            unset($data[$key]['password']);
        }

        $response->getBody()->write(json_encode($data, JSON_FORCE_OBJECT));

        return $response;
    }

    public function getById(Request $request, Response $response, string $id)
    {
        $data = $this->userRepository->getById((int) $id);

        // Should not display the password
        unset($data['password']);

        $response->getBody()->write(json_encode($data, JSON_FORCE_OBJECT));

        return $response;
    }

    public function create(Request $request, Response $response)
    {
        $body = $request->getParsedBody();
        $body['password'] ??= 'password@123'; // Default password

        $validator = $this->createValidator->withData($body);
        // Validate the request body
        if(!$validator->validate()){ // If validation fails
            $response->getBody()
                     ->write(json_encode($validator->errors(),
                                         JSON_FORCE_OBJECT));

            return $response->withStatus(422);
        }

        $username = $body['username'];
        $password = $body['password'];

        // Check if username already exists
        if ($this->userRepository->getByUsername($username) !== null) { // If username already exists
            $body = json_encode([
                'status' => 'error',
                'message' => 'Username already exists',
                'id' => null
            ], JSON_FORCE_OBJECT);

            $response->getBody()->write($body);

            return $response->withStatus(409);
        }

        $id = $this->userRepository->create($username, $password);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'Account created',
            'id' => $id
        ], JSON_FORCE_OBJECT));

        return $response;
    }

    public function updatePassword(Request $request, Response $response, string $id)
    {
        $body = $request->getParsedBody();

        $validator = $this->updatePasswordValidator->withData($body);
        // Validate the request body
        if(!$validator->validate()){ // If validation fails
            $response->getBody()
                     ->write(json_encode($validator->errors(), 
                                         JSON_FORCE_OBJECT));

            return $response->withStatus(422);
        }

        $password = $body['password'];
        $newPassword = $body['newPassword'];

        $account = $this->userRepository->getById((int) $id);

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

        $this->userRepository->updatePassword($account['id'], $newPassword);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'Password updated'
        ], JSON_FORCE_OBJECT));

        return $response;
    }
}