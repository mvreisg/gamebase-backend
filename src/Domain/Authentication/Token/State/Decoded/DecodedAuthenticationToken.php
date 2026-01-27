<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Decoded;

use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Data\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Username;

class DecodedAuthenticationToken
{
    private \DateTimeImmutable $issuedAt;
    private \DateTimeImmutable $expiresAt;
    private AuthenticationData $data;

    public function __construct(
        \DateTimeImmutable $issuedAt,
        \DateTimeImmutable $expiresAt,
        AuthenticationData $data
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

    public function getUserPermissions(): PermissionCollection
    {
        return $this->data->getPermissionCollection();
    }

    public function getUserSectors(): SectorCollection
    {
        return $this->data->getSectorCollection();
    }
}
