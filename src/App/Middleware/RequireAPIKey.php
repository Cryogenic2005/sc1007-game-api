<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;

/** Middleware for requiring an API key */
class RequireAPIKey
{
    public function __construct(private ResponseFactory $responseFactory) { }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        if (!$request->hasHeader('X-API-Key')) {
            $response = $this->responseFactory->createResponse();
            $response->getBody()
                     ->write('API Key required');

            return $response->withStatus(400);
        }

        $apiKey = $request->getHeaderLine('X-API-Key');

        if ($apiKey !== $_ENV['API_KEY']) {
            $response = $this->responseFactory->createResponse();
            $response->getBody()->write('Invalid API Key');
            
            return $response->withStatus(401);
        }

        return $handler->handle($request);
    }
}