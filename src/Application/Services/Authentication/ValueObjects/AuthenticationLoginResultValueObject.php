<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication\ValueObjects;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\Enums\AuthenticationLoginExistanceStatesEnum;

class AuthenticationLoginResultValueObject
{
    private AuthenticationLoginExistanceStatesEnum $state;
    private string $token;

    public function __construct(
        AuthenticationLoginExistanceStatesEnum $state,
        string $token
    ) {
        $this->state = $state;
        $this->token = $token;
    }

    public function getState(): AuthenticationLoginExistanceStatesEnum
    {
        return $this->state;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
