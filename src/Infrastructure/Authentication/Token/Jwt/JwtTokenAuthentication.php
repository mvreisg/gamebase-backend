<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Mvreisg\GamebaseBackend\Domain\Authentication\Enums\AuthenticationTimesEnum;
use Mvreisg\GamebaseBackend\Domain\Authentication\AuthenticationInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Exceptions\JwtTokenAuthenticationException;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\Exceptions\DotenvEnvironmentException;

class JwtTokenAuthentication implements AuthenticationInterface
{
    public function encode(string $username, AuthenticationTimesEnum $timeType): string
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
                    throw new JwtTokenAuthenticationException(
                        "JWT token authentication error: Invalid time type: $timeType."
                    );
            }
            $secretKey = DotenvEnvironment::get('JWT_SECRET');
            $issuedAt = new \DateTimeImmutable();
            $expireAt = $issuedAt->modify($time)->getTimestamp();

            $payload = [
                'iat' => $issuedAt->getTimestamp(),
                'exp' => $expireAt,
                'sub' => $username
            ];

            $token = JWT::encode($payload, $secretKey, 'HS256');

            return $token;
        } catch (DotenvEnvironmentException $e) {
            throw new JwtTokenAuthenticationException(
                "JWT token authentication error: {$e->getMessage()}"
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function decode(string $token): mixed
    {
        try {
            $secretKey = DotenvEnvironment::get('JWT_SECRET');
            $payload = JWT::decode($token, new Key($secretKey, 'HS256'));
            return $payload;
        } catch (\InvalidArgumentException $e) {
            throw new JwtTokenAuthenticationException(
                'Invalid argument!',
                $e,
            );
        } catch (\DomainException $e) {
            throw new JwtTokenAuthenticationException(
                'Domain error!',
                $e,
            );
        } catch (\UnexpectedValueException $e) {
            throw new JwtTokenAuthenticationException(
                'Unexpected value!',
                $e,
            );
        } catch (SignatureInvalidException $e) {
            throw new JwtTokenAuthenticationException(
                'Invalid signature!',
                $e,
            );
        } catch (BeforeValidException $e) {
            throw new JwtTokenAuthenticationException(
                'Before valid!',
                $e,
            );
        } catch (ExpiredException $e) {
            throw new JwtTokenAuthenticationException(
                'Expired token!',
                $e,
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
