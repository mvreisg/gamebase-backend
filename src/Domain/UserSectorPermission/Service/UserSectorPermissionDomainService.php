<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Service;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\UserSectorPermission;
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
        UserSectorPermission $userSectorPermission
    ): void {
        $sectorType = $userSectorPermission->getSector()->getSectorValue()->getValue();
        $permissionType = $userSectorPermission->getPermission()->getPermissionValue()->getValue();
        $isValid = $sectorType->allow($permissionType);
        if ($isValid === false) {
            throw new InvalidUserSectorPermissionException(
                $userSectorPermission
            );
        }
    }
}
