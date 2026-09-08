<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Service;

use Mvreisg\GamebaseBackend\Domain\Authorization\Service\AuthorizationDomainService;
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
    private AuthorizationDomainService $service;

    public function __construct(
        UserSectorPermissionRepositoryInterface $repository,
        AuthorizationDomainService $service
    ) {
        $this->repository = $repository;
        $this->service = $service;
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

    public function assertIsValid(
        User $user,
        Sector $sector,
        Permission $permission
    ): void {
        $isValid = $this->service->check($sector->getSectorValue(), $permission->getPermissionValue());
        if ($isValid === false) {
            throw new InvalidUserSectorPermissionException(
                $user,
                $sector,
                $permission
            );
        }
    }
}
