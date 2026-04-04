<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity;

use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIdException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;

class UserSectorPermission
{
    private ?Id $id;
    private User $user;
    private Sector $sector;
    private Permission $permission;

    public function __construct(
        User $user,
        Sector $sector,
        Permission $permission
    ) {
        $this->id = null;
        $this->user = $user;
        $this->sector = $sector;
        $this->permission = $permission;
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSector(): Sector
    {
        return $this->sector;
    }

    public function getPermission(): Permission
    {
        return $this->permission;
    }
}
