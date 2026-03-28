<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Authorization;

use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exceptions\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorCollection;
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

    public function check(Id $userId, SectorTypes $sectorType, PermissionTypes $permissionType): void
    {
        try {
            $this->userRepository->checkIfExists(
                $userId
            );

            $userSectorPermissions = $this->userSectorPermissionRepository->findAllByUserId($userId);
            $sectors = new SectorCollection();
            $permissions = new PermissionCollection();

            foreach ($userSectorPermissions->fetchAll() as $userSectorPermission) {
                $sector = $this->sectorRepository->findById(
                    $userSectorPermission->getSectorId()
                );
                $sectors->add($sector);

                $permission = $this->permissionRepository->findById(
                    $userSectorPermission->getPermissionId()
                );
                $permissions->add($permission);
            }

            $hasSector = count(
                array_filter(
                    $sectors->fetchAll(),
                    fn ($item) => $item->getSectorValue() === $sectorType->value
                )
            ) > 0;

            $hasPermission = count(
                array_filter(
                    $permissions->fetchAll(),
                    fn ($item) => $item->getPermissionValue() === $permissionType->value
                )
            ) > 0;

            if ($hasSector === false || $hasPermission === false) {
                throw new UnauthorizedException();
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
