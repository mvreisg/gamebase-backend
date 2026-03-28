<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityException;

class UserSectorPermission
{
    private ?Id $id;
    private Id $userId;
    private Id $sectorId;
    private Id $permissionId;

    public function __construct(Id $userId, Id $sectorId, Id $permissionId)
    {
        $this->id = null;
        $this->userId = $userId;
        $this->sectorId = $sectorId;
        $this->permissionId = $permissionId;
    }

    public function setId(Id $id): void
    {
        $this->id = $id;
    }

    public function getId(): Id
    {
        if ($this->id === null) {
            throw new EntityException(
                "The id is null."
            );
        }
        return $this->id;
    }

    public function getUserId(): Id
    {
        if ($this->userId === null) {
            throw new EntityException(
                "The userId is null."
            );
        }
        return $this->userId;
    }

    public function getSectorId(): Id
    {
        if ($this->sectorId === null) {
            throw new EntityException(
                "The sectorId is null."
            );
        }
        return $this->sectorId;
    }

    public function getPermissionId(): Id
    {
        if ($this->permissionId === null) {
            throw new EntityException(
                "The permissionId is null."
            );
        }
        return $this->permissionId;
    }
}
