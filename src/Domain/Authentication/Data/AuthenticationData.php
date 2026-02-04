<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Data;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Data\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Username;

class AuthenticationData
{
    private Id $userId;
    private Username $username;
    private PermissionCollection $permissionCollection;
    private SectorCollection $sectorCollection;

    public function __construct(
        Id $userId,
        Username $username,
        PermissionCollection $permissionCollection,
        SectorCollection $sectorCollection
    ) {
        $this->userId = $userId;
        $this->username = $username;
        $this->permissionCollection = $permissionCollection;
        $this->sectorCollection = $sectorCollection;
    }

    public static function toObject(\stdClass $data): self
    {
        return new self(
            Id::make($data->userId),
            Username::make($data->username),
            new PermissionCollection($data->permissions),
            new SectorCollection($data->sectors)
        );
    }

    public function getUserId(): Id
    {
        return $this->userId;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function getPermissionCollection(): PermissionCollection
    {
        return $this->permissionCollection;
    }

    public function getSectorCollection(): SectorCollection
    {
        return $this->sectorCollection;
    }

    public function toArray(): array
    {
        return [
            "userId" => $this->userId->getValue(),
            "username" => $this->username->getValue(),
            "permissions" => $this->permissionCollection->fetchAll(),
            "sectors" => $this->sectorCollection->fetchAll(),
        ];
    }
}
