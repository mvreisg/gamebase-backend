<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Service;

use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Exception\InvalidUserSectorPermissionException;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Exception\UserSectorPermissionNotFoundException;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\UserSectorPermissionRepositoryInterface;

class UserSectorPermissionDomainService
{
    private UserSectorPermissionRepositoryInterface $repository;

    public function __construct(
        UserSectorPermissionRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function ensureUserSectorPermissionExists(Id $id): void
    {
        $doesExist = $this->repository->checkIfExists($id);

        if ($doesExist === false) {
            throw new UserSectorPermissionNotFoundException(
                $id
            );
        }
    }

    public function assertSectorPermissionIsValid(
        User $user,
        Sector $sector,
        Permission $permission
    ): void {
        $isValid = $sector->getSectorValue()->getValue()->allow($permission->getPermissionValue()->getValue());
        if ($isValid === false) {
            throw new InvalidUserSectorPermissionException(
                $user,
                $sector,
                $permission
            );
        }
    }
}
