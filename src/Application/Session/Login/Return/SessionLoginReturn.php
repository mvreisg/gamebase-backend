<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Session\Login\Return;

use Mvreisg\GamebaseBackend\Application\Session\Data\SessionData;

class SessionLoginReturn
{
    private string $token;
    private SessionData $data;

    public function __construct(
        string $token,
        SessionData $data
    ) {
        $this->token = $token;
        $this->data = $data;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getData(): SessionData
    {
        return $this->data;
    }
}
