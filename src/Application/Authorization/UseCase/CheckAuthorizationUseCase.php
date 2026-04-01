<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Authorization\Service;

use Mvreisg\GamebaseBackend\Application\Authentication\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Collection\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Permission\Repository\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Collection\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Sector\Repository\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\UserSectorPermissionRepositoryInterface;

class CheckAuthorizationUseCase
{
    private UserRepositoryInterface $userRepository;
    private PermissionRepositoryInterface $permissionRepository;
    private SectorRepositoryInterface $sectorRepository;
    private UserSectorPermissionRepositoryInterface $userSectorPermissionRepository;
    private AuthenticationService $authenticationService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PermissionRepositoryInterface $permissionRepository,
        SectorRepositoryInterface $sectorRepository,
        UserSectorPermissionRepositoryInterface $userSectorPermissionRepository,
        AuthenticationService $authenticationService,
    ) {
        $this->userRepository = $userRepository;
        $this->permissionRepository = $permissionRepository;
        $this->sectorRepository = $sectorRepository;
        $this->userSectorPermissionRepository = $userSectorPermissionRepository;
        $this->authenticationService = $authenticationService;
    }

    public function execute(string $token, SectorType $sectorType, PermissionType $permissionType): bool
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $userId = $decodedToken->getAuthenticationData()->getUserId();

            $this->userRepository->checkIfExists(
                $userId
            );

            $userSectorPermissions = $this->userSectorPermissionRepository->findAllByUserId(
                $userId
            );
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
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
