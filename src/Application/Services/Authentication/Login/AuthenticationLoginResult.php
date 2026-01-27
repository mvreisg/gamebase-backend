<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authentication\Login;

use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;

class AuthenticationLoginResult
{
    private AuthenticationLoginStates $state;
    private EncodedAuthenticationToken $token;
    private AuthenticationData $data;

    public function __construct(
        AuthenticationLoginStates $state,
        EncodedAuthenticationToken $token,
        AuthenticationData $data
    ) {
        $this->state = $state;
        $this->token = $token;
        $this->data = $data;
    }

    public function getState(): AuthenticationLoginStates
    {
        return $this->state;
    }

    public function getToken(): EncodedAuthenticationToken
    {
        return $this->token;
    }

    public function getData(): AuthenticationData
    {
        return $this->data;
    }
}
