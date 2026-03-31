<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity;

use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIdException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

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
            throw new NullIdException(
                UserSectorPermission::class
            );
        }
        return $this->id;
    }

    public function getUserId(): Id
    {
        return $this->userId;
    }

    public function getSectorId(): Id
    {
        return $this->sectorId;
    }

    public function getPermissionId(): Id
    {
        return $this->permissionId;
    }
}
