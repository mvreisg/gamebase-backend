<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock;

use Mvreisg\GamebaseBackend\Domain\Authentication\AuthenticationInterface;
use Mvreisg\GamebaseBackend\Domain\Authentication\DTOs\AuthenticationPayloadValueDTO;
use Mvreisg\GamebaseBackend\Domain\Authentication\Enums\AuthenticationTimesEnum;
use Mvreisg\GamebaseBackend\Domain\Authentication\Interfaces\AuthenticationClockInterface;
use Mvreisg\GamebaseBackend\Domain\Authentication\ValueObjects\AuthenticationPayloadValueObject;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Exceptions\MockTokenAuthenticationException;

class MockTokenAuthentication implements AuthenticationInterface
{
    private AuthenticationClockInterface $clock;

    public function __construct(AuthenticationClockInterface $clock)
    {
        $this->clock = $clock;
    }

    public function encode(
        AuthenticationPayloadValueDTO $dto,
        AuthenticationTimesEnum $time,
        AuthenticationClockInterface $clock
    ): string {
        try {
            $seconds = 0;
            switch ($time) {
                case AuthenticationTimesEnum::OneDay:
                    $seconds = 60 * 60 * 24;
                    break;
                case AuthenticationTimesEnum::OneWeek:
                    $seconds = 60 * 60 * 24 * 7;
                    break;
                default:
                    throw new MockTokenAuthenticationException(
                        "Untreated time: $time"
                    );
            }
            $emittedAt = strval($this->clock->now()->getTimestamp());
            return base64_encode(
                join(
                    "|",
                    [
                        $seconds,
                        json_encode($dto),
                        $emittedAt
                    ]
                )
            );
        } catch (\Throwable $e) {
            throw new MockTokenAuthenticationException(
                "Authentication encode error: {$e->getMessage()}",
                $e
            );
        }
    }

    public function decode(string $token, AuthenticationClockInterface $clock): AuthenticationPayloadValueObject
    {
        try {
            $decoded = base64_decode($token);
            $split = explode("|", $decoded);
            $count = count($split);
            if ($count < 3) {
                throw new MockTokenAuthenticationException(
                    "Invalid token: invalid number of parts."
                );
            }
            $seconds = intval($split[0]);
            $payload = json_decode($split[1]);
            $emittedAt = intval($split[2]);
            if ($this->clock->now()->getTimestamp() >= $emittedAt + $seconds) {
                throw new MockTokenAuthenticationException(
                    "Invalid token: expired date."
                );
            }
            $dto = new AuthenticationPayloadValueDTO(
                $payload->userId,
                $payload->username,
                $payload->permissions,
                $payload->sectors
            );
            return new AuthenticationPayloadValueObject(
                \DateTimeImmutable::createFromFormat("U", (string)$emittedAt),
                \DateTimeImmutable::createFromFormat("U", (string)($emittedAt + $seconds)),
                $dto
            );
        } catch (MockTokenAuthenticationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new MockTokenAuthenticationException(
                "Authentication decode error: {$e->getMessage()}",
                $e
            );
        }
    }
}
