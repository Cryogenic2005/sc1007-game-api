<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Repositories\UserRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;
use App\Authorization\JWTHelper;
use App\Authorization\JWTError;

/** Middleware for validating a JWT */
class ValidateJWT
{
    public function __construct(private ResponseFactory $responseFactory,
                                private UserRepository $userRepository)
    {   
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        if (!$request->hasHeader('Authorization')) {
            $response = $this->responseFactory->createResponse();
            $response->getBody()
                     ->write(json_encode(['error' => 'Authorization header required']));

            return $response->withStatus(400);
        }

        $authorization = $request->getHeaderLine('Authorization');
        $jwt = preg_replace('/^Bearer\s*/', '', $authorization);
        
        if (empty($jwt)) {
            $response = $this->responseFactory->createResponse();
            $response->getBody()->write(json_encode(['error' => 'JWT required']));
            
            return $response->withStatus(400);
        }

        $payload = JWTHelper::getJWTPayload($jwt, $_ENV['JWT_SECRET']);

        if ($payload['status'] === JWTError::SUCCESS)
        {
            $request = $request
                ->withAttribute('sub', $payload['sub'])
                ->withAttribute('name', $payload['name']);
            return $handler->handle($request);
        }
        
        $response = $this->responseFactory->createResponse();
        $error_message = match($payload['status']) {
            JWTError::EXPIRED => 'JWT expired',
            JWTError::BEFORE_VALID => 'JWT not yet valid',
            JWTError::SIGNATURE_INVALID => 'Invalid JWT signature',
            JWTError::JWT_EXCEPTION => 'Error decoding JWT',
            JWTError::UNKNOWN => 'Unknown error',
        };
        $error_code = match($payload['status']) {
            JWTError::JWT_EXCEPTION => 400,
            JWTError::UNKNOWN => 500,
            default => 401,
        };

        $response->getBody()->write(json_encode(['error' => $error_message]));

        return $response->withStatus($error_code);
    }
}