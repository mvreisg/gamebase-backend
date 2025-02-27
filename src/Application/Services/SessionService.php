<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;
use Exception;

class SessionService
{
    public function generateToken(int $userId): string
    {
        try {
            $secretKey = $_SERVER['JWT_SECRET'];
            $issuedAt = new DateTimeImmutable();
            $expireAt = $issuedAt->modify('+60 seconds')->getTimestamp();

            $payload = [
                'iat' => $issuedAt->getTimestamp(),
                'exp' => $expireAt,
                'sub' => $userId
            ];

            return JWT::encode($payload, $secretKey, 'HS256');
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function validateToken(string $token): int
    {
        try {
            $secretKey = $_SERVER['JWT_SECRET'];
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            return $decoded->sub;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
