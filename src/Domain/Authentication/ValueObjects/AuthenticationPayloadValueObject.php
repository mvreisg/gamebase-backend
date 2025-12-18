<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\ValueObjects;

use Mvreisg\GamebaseBackend\Domain\Authentication\DTOs\AuthenticationPayloadValueDTO;

class AuthenticationPayloadValueObject
{
    private \DateTimeImmutable $emittedAt;
    private \DateTimeImmutable $expiresAt;
    private AuthenticationPayloadValueDTO $dto;

    public function __construct(
        \DateTimeImmutable $emittedAt,
        \DateTimeImmutable $expiresAt,
        AuthenticationPayloadValueDTO $dto
    ) {
        $this->emittedAt = $emittedAt;
        $this->expiresAt = $expiresAt;
        $this->dto = $dto;
    }

    public function getEmittedAt(): \DateTimeImmutable
    {
        return $this->emittedAt;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getDto(): AuthenticationPayloadValueDTO
    {
        return $this->dto;
    }
}
