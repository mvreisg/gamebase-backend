<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Session\Login;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Session\Data\SessionData;

class SessionLoginResult
{
    private SessionLoginStates $state;
    private EncodedAuthenticationToken $token;
    private SessionData $data;

    public function __construct(
        SessionLoginStates $state,
        EncodedAuthenticationToken $token,
        SessionData $data
    ) {
        $this->state = $state;
        $this->token = $token;
        $this->data = $data;
    }

    public function getState(): SessionLoginStates
    {
        return $this->state;
    }

    public function getToken(): EncodedAuthenticationToken
    {
        return $this->token;
    }

    public function getData(): SessionData
    {
        return $this->data;
    }
}
