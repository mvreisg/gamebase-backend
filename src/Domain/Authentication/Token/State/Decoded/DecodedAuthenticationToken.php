<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Decoded;

use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Session\Data\SessionData;

class DecodedAuthenticationToken
{
    private \DateTimeImmutable $issuedAt;
    private \DateTimeImmutable $expiresAt;
    private SessionData $data;

    public function __construct(
        \DateTimeImmutable $issuedAt,
        \DateTimeImmutable $expiresAt,
        SessionData $data
    ) {
        $this->issuedAt = $issuedAt;
        $this->expiresAt = $expiresAt;
        $this->data = $data;
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
        return $this->data->getUserId();
    }

    public function getUsername(): Username
    {
        return $this->data->getUsername();
    }

    public function getUserSectorPermissionCollection(): UserSectorPermissionCollection
    {
        return $this->data->getUserSectorPermissionCollection();
    }
}
