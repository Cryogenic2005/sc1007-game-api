<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
use App\Repositories\UserRepository;

/** Middleware for validating user ID */
class ValidateId
{
    public function __construct(private UserRepository $userRepository) { }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $routeContext = RouteContext::fromRequest($request);

        $route = $routeContext->getRoute();

        $id = $route->getArgument('id');

        if ($this->userRepository->getById((int) $id) === null) {
            throw new \Slim\Exception\HttpNotFoundException($request);
        }
        
        $jwt_id = $request->getAttribute('id');

        if ($jwt_id !== (int) $id) {
            throw new \Slim\Exception\HttpForbiddenException($request, 'You do not have permission to access this resource');
        }

        return $handler->handle($request);
    }
}