<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt;

use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Mvreisg\GamebaseBackend\Domain\Authentication\Enums\AuthenticationTimesEnum;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\TokenAuthenticationInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Authentication\AuthenticationException;

class JwtTokenAuthentication implements TokenAuthenticationInterface
{
    public function encode(string $userName, AuthenticationTimesEnum $timeType): string
    {
        try {
            $time = '';
            switch ($timeType) {
                case AuthenticationTimesEnum::OneDay:
                    $time = '+1 day';
                    break;
                case AuthenticationTimesEnum::OneWeek:
                    $time = '+1 week';
                    break;
                default:
                    throw new AuthenticationException(
                        'Untreated time: ' . $timeType
                    );
            }
            $secretKey = DotenvEnvironment::get('JWT_SECRET');
            $issuedAt = new DateTimeImmutable();
            $expireAt = $issuedAt->modify($time)->getTimestamp();

            $payload = [
                'iat' => $issuedAt->getTimestamp(),
                'exp' => $expireAt,
                'sub' => $userName
            ];

            $token = JWT::encode($payload, $secretKey, 'HS256');

            return $token;
        } catch (\Throwable $e) {
            throw new AuthenticationException(
                'JWT encode error!',
                $e
            );
        }
    }

    public function decode(string $token): mixed
    {
        try {
            $secretKey = DotenvEnvironment::get('JWT_SECRET');
            $payload = JWT::decode($token, new Key($secretKey, 'HS256'));
            return $payload;
        } catch (\Throwable $e) {
            throw new AuthenticationException(
                'JWT decode error!',
                $e
            );
        }
    }
}
