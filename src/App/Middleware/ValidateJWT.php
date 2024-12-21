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
                     ->write('Authorization header required');

            return $response->withStatus(400);
        }

        $authorization = $request->getHeaderLine('Authorization');
        $jwt = preg_replace('/^Bearer\s*/', '', $authorization);
        
        if (empty($jwt)) {
            $response = $this->responseFactory->createResponse();
            $response->getBody()->write('JWT required');
            
            return $response->withStatus(400);
        }

        $payload = JWTHelper::getJWTPayload($jwt, $_ENV['JWT_SECRET']);
        
        switch ($payload['status']) {
            case JWTError::SUCCESS:
                # Validate username
                $account = $this->userRepository->getById($payload['sub']);
                if ($account === null) {
                    $response = $this->responseFactory->createResponse();
                    $response->getBody()->write('Invalid JWT');
                    
                    return $response->withStatus(400);
                }
        
                # Validate user ID
                if ($account['id'] !== $payload['sub']) {
                    $response = $this->responseFactory->createResponse();
                    $response->getBody()->write('Invalid JWT');
                    
                    return $response->withStatus(400);
                }

                $request = $request
                    ->withAttribute('id', $payload['sub'])
                    ->withAttribute('name', $payload['name']);
                return $handler->handle($request);
            case JWTError::EXPIRED:
                $response = $this->responseFactory->createResponse();
                $response->getBody()->write('JWT expired');
                
                return $response->withStatus(401);
            case JWTError::BEFORE_VALID:
                $response = $this->responseFactory->createResponse();
                $response->getBody()->write('JWT not yet valid');
                
                return $response->withStatus(401);
            case JWTError::SIGNATURE_INVALID:
                $response = $this->responseFactory->createResponse();
                $response->getBody()->write('JWT signature invalid');
                
                return $response->withStatus(401);
            case JWTError::JWT_EXCEPTION:
                $response = $this->responseFactory->createResponse();
                $response->getBody()->write('Invalid JWT');
                
                return $response->withStatus(400);
            default:
                throw new \Slim\Exception\HttpInternalServerErrorException($request);
        }
    }
}