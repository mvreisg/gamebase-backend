<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded;

use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;

class DecodedAuthenticationToken
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

    public function getUserId(): Id
    {
        return $this->authenticationData->getUserId();
    }

    public function getUsername(): Username
    {
        return $this->authenticationData->getUsername();
    }
}
