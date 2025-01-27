<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Repositories\UserRepository;

/** Controller for managing actions on admin accounts */
class Admin
{
    public function __construct(private UserRepository $userRepository) { }

    /** Get all admin accounts */
    public function getAll(Request $request, Response $response)
    {
        $data = $this->userRepository->getAllAdmins();

        // Should not display the password
        foreach ($data as $key => $value) {
            unset($data[$key]['password']);
        }

        $response->getBody()->write(json_encode($data, JSON_FORCE_OBJECT));

        return $response;
    }

    public function setAdmin(Request $request, Response $response, string $id)
    {
        $this->userRepository->updateAdminStatus((int) $id, true);

        $response->getBody()->write(json_encode(['message' => 'User is now an admin'], JSON_FORCE_OBJECT));

        return $response;
    }

    public function unsetAdmin(Request $request, Response $response, string $id)
    {
        $this->userRepository->updateAdminStatus((int) $id, false);

        $response->getBody()->write(json_encode(['message' => 'User is no longer an admin'], JSON_FORCE_OBJECT));

        return $response;
    }
}