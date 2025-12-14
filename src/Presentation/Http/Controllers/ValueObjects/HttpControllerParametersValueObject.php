<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\ValueObjects;

class HttpControllerParametersValueObject
{
    private bool $verifyAuthentication;

    public function __construct(bool $verifyAuthentication)
    {
        $this->verifyAuthentication = $verifyAuthentication;
    }

    public function shouldVerifyAuthentication(): bool
    {
        return $this->verifyAuthentication;
    }
}
