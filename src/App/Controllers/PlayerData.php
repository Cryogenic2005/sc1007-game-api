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
            "id" => ["required"],
            "data.name" => ["required"],
            "data.time" => ["optional", "integer"],
            "data.attempts" => ["optional", "integer"]
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

    public function updateData(Request $request, Response $response)
    {
        $body = $request->getParsedBody();
        $validator = $this->updateValidator->withData($body);
        if (!$validator->validate()) {
            $response->getBody()
                     ->write(json_encode($validator->errors(), JSON_FORCE_OBJECT));
            return $response->withStatus(400);
        }

        $id = $body["id"];
        $data = $body["data"];

        if (!$this->playerDataRepository->hasRecord($id, $data["name"])) {
            $this->playerDataRepository->createRecord($id, $data["name"]);
        }

        if (isset($data["time"])) {
            $this->playerDataRepository->updateRecordTime($id, $data["name"], $data["time"]);
        }

        if (isset($data["attempts"])) {
            $this->playerDataRepository->updateRecordAttempts($id, $data["name"], $data["attempts"]);
        }

        $response->getBody()->write(json_encode([
            "status" => "success",
            "message" => "Player data updated"
        ], JSON_FORCE_OBJECT));

        return $response;
    }
}