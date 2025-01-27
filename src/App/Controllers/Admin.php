<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Repositories\UserRepository;
use Valitron\Validator;

/** Controller for managing actions on admin accounts */
class Admin
{
    private Validator $updateAdminValidator;

    public function __construct(private UserRepository $userRepository)
    {
        $this->updateAdminValidator = new Validator([]);
        $this->updateAdminValidator->rule('required', 'isAdmin');
        $this->updateAdminValidator->rule('in', 'isAdmin', [0, 1]); // 0 = false, 1 = true
    }

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

    public function updateAdmin(Request $request, Response $response, string $id)
    {
        $data = $request->getParsedBody();

        $validator = $this->updateAdminValidator->withData($data);
        if(!$validator->validate()){
            $response->getBody()
                     ->write(json_encode($validator->errors(), JSON_FORCE_OBJECT));

            return $response->withStatus(422);
        }

        if (!isset($data['isAdmin'])) {
            $response->getBody()->write(json_encode(['error' => 'isAdmin field is required'], JSON_FORCE_OBJECT));
            return $response->withStatus(400);
        }

        if ($data['isAdmin'] === 'true') {
            return $this->setAdmin($request, $response, $id);
        }

        return $this->unsetAdmin($request, $response, $id);
    }

    private function setAdmin(Request $request, Response $response, string $id)
    {
        $this->userRepository->updateAdminStatus((int) $id, true);

        $response->getBody()->write(json_encode(['message' => 'User is now an admin'], JSON_FORCE_OBJECT));

        return $response;
    }

    private function unsetAdmin(Request $request, Response $response, string $id)
    {
        $this->userRepository->updateAdminStatus((int) $id, false);

        $response->getBody()->write(json_encode(['message' => 'User is no longer an admin'], JSON_FORCE_OBJECT));

        return $response;
    }
}