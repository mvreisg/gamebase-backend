<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\UserSectorPermission;

use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;

class UserSectorPermissionService
{
    private UserRepositoryInterface $userRepository;
    private SectorRepositoryInterface $sectorRepository;
    private PermissionRepositoryInterface $permissionRepository;
    private UserSectorPermissionRepositoryInterface $userSectorPermissionRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SectorRepositoryInterface $sectorRepository,
        PermissionRepositoryInterface $permissionRepository,
        UserSectorPermissionRepositoryInterface $userSectorPermissionRepository
    ) {
        $this->userRepository = $userRepository;
        $this->sectorRepository = $sectorRepository;
        $this->permissionRepository = $permissionRepository;
        $this->userSectorPermissionRepository = $userSectorPermissionRepository;
    }

    public function insert(UserSectorPermission $new): UserSectorPermission
    {
        try {
            $this->userRepository->checkIfExists(
                Id::make($new->getUserIdValue())
            );

            $this->sectorRepository->checkIfExists(
                Id::make($new->getSectorIdValue())
            );

            $this->permissionRepository->checkIfExists(
                Id::make($new->getPermissionIdValue())
            );

            $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert($new);

            return $insertedUserSectorPermission;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(UserSectorPermission $existant): bool
    {
        try {
            $this->userSectorPermissionRepository->checkIfExists(
                Id::make($existant->getIdValue())
            );

            $this->userRepository->checkIfExists(
                Id::make($existant->getUserIdValue())
            );

            $this->sectorRepository->checkIfExists(
                Id::make($existant->getSectorIdValue())
            );

            $this->permissionRepository->checkIfExists(
                Id::make($existant->getPermissionIdValue())
            );

            $wasUpdated = $this->userSectorPermissionRepository->update($existant);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(Id $id): bool
    {
        try {
            $this->userSectorPermissionRepository->checkIfExists($id);

            $wasDeleted = $this->userSectorPermissionRepository->delete($id);

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): UserSectorPermission
    {
        try {
            $fetchedUserPermission = $this->userSectorPermissionRepository->findById(
                $id
            );

            return $fetchedUserPermission;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): UserSectorPermissionCollection
    {
        try {
            return $this->userSectorPermissionRepository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
