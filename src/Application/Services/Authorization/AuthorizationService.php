<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authorization;

use Mvreisg\GamebaseBackend\Domain\Authorization\Types\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exceptions\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;

class AuthorizationService
{
    private UserRepositoryInterface $userRepository;
    private PermissionRepositoryInterface $permissionRepository;
    private SectorRepositoryInterface $sectorRepository;
    private UserSectorPermissionRepositoryInterface $userSectorPermissionRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PermissionRepositoryInterface $permissionRepository,
        SectorRepositoryInterface $sectorRepository,
        UserSectorPermissionRepositoryInterface $userSectorPermissionRepository
    ) {
        $this->userRepository = $userRepository;
        $this->permissionRepository = $permissionRepository;
        $this->sectorRepository = $sectorRepository;
        $this->userSectorPermissionRepository = $userSectorPermissionRepository;
    }

    public function check(
        UserSectorPermissionCollection $userSectorPermissionCollection,
        SectorTypes $sectorType,
        PermissionTypes $permissionType
    ): void {
        try {
            foreach ($userSectorPermissionCollection->fetchAll() as $userSectorPermission) {
                $this->userSectorPermissionRepository->checkIfExists(
                    Id::make($userSectorPermission->getIdValue())
                );
                $this->userRepository->checkIfExists(
                    Id::make($userSectorPermission->getUserIdValue())
                );
                $sector = $this->sectorRepository->findById(
                    Id::make($userSectorPermission->getSectorIdValue())
                );
                $permission = $this->permissionRepository->findById(
                    Id::make($userSectorPermission->getPermissionIdValue())
                );
                if ($sector->getSectorValue() !== $sectorType->value) {
                    continue;
                }
                if ($permission->getPermissionValue() !== $permissionType->value) {
                    continue;
                }
                return;
            }
            throw new UnauthorizedException();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
