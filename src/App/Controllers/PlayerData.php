<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Valitron\Validator;

class PlayerData
{
    private Validator $updateValidator;

    public function __construct()
    {
        $this->updateValidator = new Validator();
        $this->updateValidator->mapFieldsRules([
            "id" => ["required"],
            "data" => ["required"]
        ]);
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

        // Update the data in the database by iterating over the data array
        // and updating the corresponding fields in the database
        // The key in the data array is the field name and the value is the new value

        $updatedFieldsStatus = [];
        foreach ($data as $field => $value) {
            // TODO: Update the field in the database and store the status of the operation
            // The following line is a placeholder for the actual update operation
            $status = true;

            // Add the status of the update operation to the response
            $updatedFieldsStatus[$field] = $status;
        }

        $responseBody = json_encode([
            "updatedFieldsStatus" => $updatedFieldsStatus
        ], JSON_FORCE_OBJECT);

        $response->getBody()
                 ->write($responseBody);

        return $response;
    }
}