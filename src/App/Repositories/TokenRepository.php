<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Authorization\JWTHelper;
use App\Authorization\JWTError;
use App\Authorization\JWTType;

/** Repository for managing refresh tokens */
class TokenRepository
{
    private const REFRESH_TOKEN_DURATION = 86400; // 1 day
    private const TABLE = 'refresh_tokens';

    public function __construct(private \PDO $pdo) {}

    /**
     * Validate a refresh token by checking if it exists in the database and is not expired
     * @param string $refreshToken The refresh token to validate
     * @return bool|null True if the token is valid, false if it is expired,
     * or null if an error occurs on the server side
     */
    public function validateToken(string $refreshToken): bool | null
    {
        $tokenHash = hash('sha256', $refreshToken);

        $query = "SELECT * FROM " . self::TABLE . " WHERE refresh_token_hash = :refresh_token_hash";
        $stmt = $this->pdo->prepare($query);

        $stmt->execute(['refresh_token_hash' => $tokenHash]);

        $token_data = $stmt->fetch();

        // If no matching token is found, return false
        if (!$token_data) return false;

        // Refresh token found, only need to check if it is expired
        // If any other error occurs, this indicates a problem on the server side
        // Return null to indicate a server error

        $payload = JWTHelper::getJWTPayload($refreshToken);

        // If the token is expired, delete it and return false
        if ($payload['status'] === JWTError::EXPIRED) {
            $this->deleteToken($refreshToken); // Delete the token if it is expired
            return false;
        }

        // If the token is invalid for any other reason,
        // return null to indicate a server error
        if ($payload['status'] !== JWTError::SUCCESS) return null;

        // If using the wrong token type, return false
        if ($payload['token_type'] !== JWTType::REFRESH->value) return false;

        return true; // Valid token
    }

    /**
     * Create a new refresh token in the database, or replace an existing one
     * @param string $sub The user ID of the token
     * @param string $name The name of the token
     * @return array The payload and token of the JWT (keyed by 'payload' and 'token')
     */
    public function createRefreshToken(int $sub, string $name): array
    {
        $this->deleteTokensForUser($sub); // Delete any existing tokens for the user
        
        $generatedTokenInfo = JWTHelper::createJWT($sub, $name, self::REFRESH_TOKEN_DURATION, JWTType::REFRESH);

        $tokenHash = hash('sha256', $generatedTokenInfo['token']);

        $query = "INSERT INTO " . self::TABLE . " (id, refresh_token_hash) VALUES (:id, :refresh_token_hash)";
        $stmt = $this->pdo->prepare($query);
        
        $stmt->execute(['id' => $sub, 'refresh_token_hash' => $tokenHash]);

        return $generatedTokenInfo;
    }

    /**
     * Delete a refresh token from the database
     * @param string $refreshToken The refresh token to delete
     */
    public function deleteToken(string $refreshToken): void
    {
        $tokenHash = hash('sha256', $refreshToken);

        $query = "DELETE FROM " . self::TABLE . " WHERE refresh_token_hash = :refresh_token_hash";
        $stmt = $this->pdo->prepare($query);

        $stmt->execute(['refresh_token_hash' => $tokenHash]);
    }

    /**
     * Delete all refresh tokens for a user
     * @param string $sub The user ID to delete tokens for
     */
    public function deleteTokensForUser(int $sub): void
    {
        $query = "DELETE FROM " . self::TABLE . " WHERE id = :id";
        $stmt = $this->pdo->prepare($query);

        $stmt->execute(['id' => $sub]);
    }
}