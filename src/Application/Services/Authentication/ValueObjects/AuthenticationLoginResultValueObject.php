<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication\ValueObjects;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\Enums\AuthenticationLoginExistanceStatesEnum;
use Mvreisg\GamebaseBackend\Domain\Authentication\DTOs\AuthenticationPayloadValueDTO;

class AuthenticationLoginResultValueObject
{
    private AuthenticationLoginExistanceStatesEnum $state;
    private string $token;
    private AuthenticationPayloadValueDTO $dto;

    public function __construct(
        AuthenticationLoginExistanceStatesEnum $state,
        string $token,
        AuthenticationPayloadValueDTO $dto
    ) {
        $this->state = $state;
        $this->token = $token;
        $this->dto = $dto;
    }

    public function getState(): AuthenticationLoginExistanceStatesEnum
    {
        return $this->state;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getDto(): AuthenticationPayloadValueDTO
    {
        return $this->dto;
    }
}
