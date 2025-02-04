<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Repositories\PlayerDataRepository;
use Valitron\Validator;

class PlayerData
{
    private Validator $updateValidator;

    public function __construct(private PlayerDataRepository $playerDataRepository)
    {
        $this->updateValidator = new Validator();
        $this->updateValidator->mapFieldsRules([
            "name" => ["required"],
            "time" => ["optional", "integer"],
            "attempts" => ["optional", "integer"]
        ]);
    }

    public function getAllData(Request $request, Response $response, string $id)
    {
        $data = $this->playerDataRepository->getRecord((int) $id);

        $response->getBody()->write(json_encode($data, JSON_FORCE_OBJECT));
        return $response;
    }

    public function getPuzzleData(Request $request, Response $response, string $id, string $name)
    {
        $data = $this->playerDataRepository->getRecord((int) $id, $name);

        $response->getBody()->write(json_encode($data[0], JSON_FORCE_OBJECT));
        return $response;
    }

    public function updateData(Request $request, Response $response, string $id)
    {
        $body = $request->getParsedBody();
        $validator = $this->updateValidator->withData($body);
        if (!$validator->validate()) {
            $response->getBody()
                     ->write(json_encode($validator->errors(), JSON_FORCE_OBJECT));
            return $response->withStatus(400);
        }

        $id = (int) $id; // Cast to integer
        $name = $body["name"];
        $time = $body["time"] ?? null;
        $attempts = $body["attempts"] ?? null;

        if (!$this->playerDataRepository->hasRecord($id, $name)) {
            $this->playerDataRepository->createRecord($id, $name);
        }

        if ($time !== null) {
            $this->playerDataRepository->updateRecordTime($id, $name, $time);
        }

        if ($attempts !== null) {
            $this->playerDataRepository->updateRecordAttempts($id, $name, $attempts);
        }

        $response->getBody()->write(json_encode([
            "status" => "success",
            "message" => "Player data updated"
        ], JSON_FORCE_OBJECT));

        return $response;
    }
}