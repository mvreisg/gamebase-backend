<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Authentication\Token;

use Mvreisg\GamebaseBackend\Application\Authentication\Data\AuthenticationData;

class AuthenticationToken
{
    private \DateTimeImmutable $issuedAt;
    private \DateTimeImmutable $expiresAt;
    private AuthenticationData $authenticationData;

    public function __construct(
        \DateTimeImmutable $issuedAt,
        \DateTimeImmutable $expiresAt,
        AuthenticationData $authenticationData
    ) {
        $this->issuedAt = $issuedAt;
        $this->expiresAt = $expiresAt;
        $this->authenticationData = $authenticationData;
    }

    public function getIssuedAt(): \DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getAuthenticationData(): AuthenticationData
    {
        return $this->authenticationData;
    }
}
