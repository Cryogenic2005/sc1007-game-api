<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Repositories\PlayerDataRepository;

class PlayerData
{
    public function __construct(private PlayerDataRepository $playerDataRepository) { }

    public function getAll(Request $request, Response $response): Response
    {
        $playerData = $this->playerDataRepository->getAll();

        $response->getBody()->write(json_encode($playerData));

        return $response;
    }

    public function getById(Request $request, Response $response, string $id): Response
    {
        $playerData = $this->playerDataRepository->getById((int) $id);

        $response->getBody()->write(json_encode($playerData));

        return $response;
    }

    public function updateData(Request $request, Response $response, string $id): Response
    {
        $body = $request->getParsedBody();

        // TODO: Validate the body: table, data

        $table = $body['table'];
        $data = $body['data'];

        // TODO: Check data fields

        // TODO: Handle updating data

        $response->getBody()->write(json_encode(['message' => 'Data updated']));

        return $response;
    }
}