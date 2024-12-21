<?php

declare(strict_types=1);

namespace App\Authorization;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;

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
    private const EXPIRATION = 3600; // 1 hour
    private const HEADER = ['typ' => 'JWT', 'alg' => self::ALGORITHM];

    public static function createJWT(array $data): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + self::EXPIRATION;
        $payload = array(
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'sub' => $data['id'],
            'name' => $data['name']
        );

        return self::encodeJWT($payload, self::HEADER);
    }

    public static function getJWTPayload(string $jwt, string $secret): array
    {
        $payload = null;
        $status = JWTError::SUCCESS;
        try {
            $payload = self::decodeJWT($jwt);
        } catch (ExpiredException $e) {
            $status = JWTError::EXPIRED;
        } catch (BeforeValidException $e) {
            $status = JWTError::BEFORE_VALID;
        } catch (SignatureInvalidException $e) {
            $status = JWTError::SIGNATURE_INVALID;
        } catch (\UnexpectedValueException | \DomainException) {
            $status = JWTError::JWT_EXCEPTION;
        } catch (\Exception $e) {
            $status = JWTError::UNKNOWN;
        }

        return [
            'status' => $status,
            'sub' => $payload->sub ?? null,
            'name' => $payload->name ?? null
        ];
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