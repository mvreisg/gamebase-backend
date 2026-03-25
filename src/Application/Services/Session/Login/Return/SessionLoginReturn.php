<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Session\Login\Return;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Session\Data\SessionData;

class SessionLoginReturn
{
    private EncodedAuthenticationToken $token;
    private SessionData $data;

    public function __construct(
        EncodedAuthenticationToken $token,
        SessionData $data
    ) {
        $this->token = $token;
        $this->data = $data;
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
