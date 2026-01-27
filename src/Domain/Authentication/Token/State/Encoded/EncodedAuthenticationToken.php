<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded;

class EncodedAuthenticationToken
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
