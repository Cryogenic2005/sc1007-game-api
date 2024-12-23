<?php

declare(strict_types=1);

namespace App\Authorization;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;

/** Enum for types of JWTokens */
enum JWTType : string
{
    case ACCESS = 'ACCESS';
    case REFRESH = 'REFRESH';
}

enum JWTError
{
    case SUCCESS;
    case EXPIRED;
    case BEFORE_VALID;
    case SIGNATURE_INVALID;
    case JWT_EXCEPTION;
    case UNKNOWN;
}

/** Helper class for JWT */
class JWTHelper
{
    private const ALGORITHM = 'HS256';

    /**
     * Create a JWToken for a user
     * @param string $sub The user ID owning this JWT
     * @param string $name The user name owning this JWT
     * @param int $duration The duration of the JWT in seconds
     * @param array $headers The headers to include in the JWT
     * @return array The payload and token of the JWT (keyed by 'payload' and 'token')
     */
    public static function createJWT(
        int $sub,
        string $name,
        int $duration,
        JWTType $tokenType): array
    {
        $headers = [
            'typ' => 'JWT',
            'alg' => self::ALGORITHM
        ];

        $issuedAt = time();
        $expirationTime = $issuedAt + $duration;
        $payload = array(
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'token_type' => $tokenType->value,
            'sub' => $sub,
            'name' => $name
        );

        return [
            'payload' => $payload,
            'token' => self::encodeJWT($payload, $headers)
        ];
    }

    /**
     * Get the payload of a JWT
     * @param string $jwt The JWT to get the payload from
     * @return array The payload of the JWT
     */
    public static function getJWTPayload(string $jwt): array
    {
        $payload = null;
        $status = JWTError::SUCCESS;
        try {
            $payload = self::decodeJWT($jwt);
        } catch (\Exception $e){
            $status = match($e::class) {
                ExpiredException::class => JWTError::EXPIRED,
                BeforeValidException::class => JWTError::BEFORE_VALID,
                SignatureInvalidException::class => JWTError::SIGNATURE_INVALID,
                \UnexpectedValueException::class, \DomainException::class => JWTError::JWT_EXCEPTION,
                default => JWTError::UNKNOWN
            };
        }

        return [
            'status' => $status,
            'token_type' => $payload->token_type ?? null,
            'exp' => $payload->exp ?? null,
            'sub' => $payload->sub ?? null,
            'name' => $payload->name ?? null
        ];
    }

    public static function verifyJWTPayload(string $jwt, string $type = null, int $id = null, string $name = null): bool
    {
        $header = base64_decode(explode('.', $jwt)[0]);
        $payload = base64_decode(explode('.', $jwt)[1]);

        $header = json_decode($header);
        $payload = json_decode($payload);

        if ($type !== null && $header->typ !== $type) return false;

        if ($id !== null && $payload->sub !== $id) return false;

        if ($name !== null && $payload->name !== $name) return false; 

        return true;
    }

    /**
     * Encode a JWT
     * @param array $payload The payload to encode
     * @param array $header The header to encode
     * @return string The encoded JWT
     */
    private static function encodeJWT(array $payload, array $header): string
    {
        return JWT::encode($payload, $_ENV['JWT_SECRET'], self::ALGORITHM, head: $header);
    }

    /**
     * Decode a JWT
     * @param string $jwt The JWT to decode
     * @return object The decoded JWT payload
     * @throws ExpiredException If the JWT is expired
     * @throws BeforeValidException If the JWT is not yet valid
     * @throws SignatureInvalidException If the JWT signature is invalid
     * @throws \UnexpectedValueException Provided JWT was invalid
     * @throws \DomainException Provided JWT is malformed
     * @throws \InvalidArgumentException Provided key/key-array was empty or malformed
     */
    private static function decodeJWT(string $jwt): object
    {
        return JWT::decode($jwt, new Key($_ENV['JWT_SECRET'], self::ALGORITHM));
    }
}