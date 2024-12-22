<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\JWTHelper;
use App\Authorization\JWTType;
use App\Repositories\TokenRepository;
use Valitron\Validator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/** Controller for managing JSON Web Refresh Token */
class TokenIssuer
{
    private const ACCESS_TOKEN_DURATION = 3600; // 1 hour
    private Validator $refreshTokenValidator;

    public function __construct(private TokenRepository $tokenRepository)
    {
        $this->refreshTokenValidator = new Validator();
        $this->refreshTokenValidator->mapFieldsRules([
            'id' => ['required'],
            'name' => ['required'],
            'refresh_token' => ['required']
        ]);
    }
    
    public function __invoke(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        $validator = $this->refreshTokenValidator->withData($body);
        if (!$validator->validate()) {
            $response->getBody()
                     ->write(json_encode($this->refreshTokenValidator->errors(), JSON_FORCE_OBJECT));
            return $response->withStatus(400);
        }

        $id = $body['id'];
        $name = $body['name'];
        $refresh_token = $body['refresh_token'];

        $isTokenValid = $this->tokenRepository->validateToken($refresh_token);
        if ($isTokenValid === null) { // Null means there was an internal error
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Internal error',
                'exp' => null,
                'token' => null
            ], JSON_FORCE_OBJECT));
            return $response->withStatus(500);
        }

        if (!$isTokenValid || !JWTHelper::verifyJWTPayload($refresh_token, id: $id, name: $name)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Invalid token',
                'exp' => null,
                'token' => null
            ], JSON_FORCE_OBJECT));
            return $response->withStatus(401);
        }
        
        $generatedTokenInfo = JWTHelper::createJWT($id, $name, self::ACCESS_TOKEN_DURATION, JWTType::ACCESS);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'Access token created',
            'exp' => $generatedTokenInfo['payload']['exp'],
            'token' => $generatedTokenInfo['token']
        ], JSON_FORCE_OBJECT));

        return $response;
    }
}