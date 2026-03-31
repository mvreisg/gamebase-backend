<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Session\Login\Return;

use Mvreisg\GamebaseBackend\Application\Session\Data\SessionData;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Data\Encoded\JwtEncodedAuthenticationToken;

class SessionLoginReturn
{
    private JwtEncodedAuthenticationToken $token;
    private SessionData $data;

    public function __construct(
        JwtEncodedAuthenticationToken $token,
        SessionData $data
    ) {
        $this->token = $token;
        $this->data = $data;
    }

    public function getToken(): JwtEncodedAuthenticationToken
    {
        return $this->token;
    }

    public function getData(): SessionData
    {
        return $this->data;
    }
}
