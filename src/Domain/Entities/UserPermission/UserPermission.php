<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities\UserPermission;

use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission\Exceptions\UserPermissionInvalidIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission\Exceptions\UserPermissionInvalidPermissionIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission\Exceptions\UserPermissionInvalidUserIdException;

class UserPermission
{
    private ?int $id;
    private int $userId;
    private int $permissionId;

    public function __construct(?int $id = null, int $userId = 0, int $permissionId = 0)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->permissionId = $permissionId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getPermissionId(): int
    {
        return $this->permissionId;
    }

    public function setPermissionId(int $permissionId): void
    {
        $this->permissionId = $permissionId;
    }

    public function validateId(): void
    {
        if ($this->id <= 0) {
            throw new UserPermissionInvalidIdException(
                'The id must be greater than zero!'
            );
        }
    }

    public function validateUserId(): void
    {
        if ($this->userId <= 0) {
            throw new UserPermissionInvalidUserIdException(
                'The user id must be greater than zero!'
            );
        }
    }

    public function validatePermissionId(): void
    {
        if ($this->permissionId <= 0) {
            throw new UserPermissionInvalidPermissionIdException(
                'The permission id must be greater than zero!'
            );
        }
    }
}
