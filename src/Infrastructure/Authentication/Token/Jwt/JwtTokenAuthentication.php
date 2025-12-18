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
use Mvreisg\GamebaseBackend\Domain\Authentication\DTOs\AuthenticationPayloadValueDTO;
use Mvreisg\GamebaseBackend\Domain\Authentication\Interfaces\AuthenticationClockInterface;
use Mvreisg\GamebaseBackend\Domain\Authentication\ValueObjects\AuthenticationPayloadValueObject;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Exceptions\JwtTokenAuthenticationException;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\Exceptions\DotenvEnvironmentException;

class JwtTokenAuthentication implements AuthenticationInterface
{
    public function encode(
        AuthenticationPayloadValueDTO $dto,
        AuthenticationTimesEnum $time,
        AuthenticationClockInterface $clock
    ): string {
        try {
            switch ($time) {
                case AuthenticationTimesEnum::OneDay:
                    $time = '+1 day';
                    break;
                case AuthenticationTimesEnum::OneWeek:
                    $time = '+1 week';
                    break;
                default:
                    throw new JwtTokenAuthenticationException(
                        "JWT token authentication encode error: Invalid time: $time."
                    );
            }
            $secretKey = DotenvEnvironment::get('JWT_SECRET');
            $issuedAt = $clock->now();
            $expireAt = $issuedAt->modify($time)->getTimestamp();

            $payload = [
                'iat' => $issuedAt->getTimestamp(),
                'exp' => $expireAt,
                'sub' => $dto
            ];

            $token = JWT::encode($payload, $secretKey, 'HS256');

            return $token;
        } catch (DotenvEnvironmentException $e) {
            throw new JwtTokenAuthenticationException(
                "JWT token authentication encode error: {$e->getMessage()}"
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function decode(string $token, AuthenticationClockInterface $clock): AuthenticationPayloadValueObject
    {
        try {
            $secretKey = DotenvEnvironment::get('JWT_SECRET');
            $payload = JWT::decode($token, new Key($secretKey, 'HS256'));
            $dto = new AuthenticationPayloadValueDTO(
                $payload->sub->username,
                $payload->sub->permissions,
                $payload->sub->sectors
            );
            return new AuthenticationPayloadValueObject(
                \DateTimeImmutable::createFromFormat('U', (string)$payload->iat),
                \DateTimeImmutable::createFromFormat('U', (string)$payload->exp),
                $dto
            );
        } catch (\InvalidArgumentException $e) {
            throw new JwtTokenAuthenticationException(
                'JWT token authentication decode error: Invalid argument.',
                $e,
            );
        } catch (\DomainException $e) {
            throw new JwtTokenAuthenticationException(
                'JWT token authentication decode error: Domain error.',
                $e,
            );
        } catch (\UnexpectedValueException $e) {
            throw new JwtTokenAuthenticationException(
                'JWT token authentication decode error: Unexpected value.',
                $e,
            );
        } catch (SignatureInvalidException $e) {
            throw new JwtTokenAuthenticationException(
                'JWT token authentication decode error: Invalid signature.',
                $e,
            );
        } catch (BeforeValidException $e) {
            throw new JwtTokenAuthenticationException(
                'JWT token authentication decode error: Before valid.',
                $e,
            );
        } catch (ExpiredException $e) {
            throw new JwtTokenAuthenticationException(
                'JWT token authentication decode error: Expired token.',
                $e,
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
