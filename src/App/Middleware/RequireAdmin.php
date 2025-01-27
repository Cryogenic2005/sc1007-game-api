<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use App\Repositories\UserRepository;

class RequireAdmin
{
    public function __construct(private UserRepository $userRepository) { }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $jwt_id = $request->getAttribute('id');

        if (!$this->userRepository->getById((int) $jwt_id)['isAdmin']) {
            throw new \Slim\Exception\HttpForbiddenException($request, 'You do not have permission to access this resource');
        }

        return $handler->handle($request);
    }
}