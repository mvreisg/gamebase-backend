<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\SectorPermission;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermission;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;

class SectorPermissionService
{
    private SectorRepositoryInterface $sectorRepository;
    private PermissionRepositoryInterface $permissionRepository;
    private SectorPermissionRepositoryInterface $sectorPermissionRepository;

    public function __construct(
        SectorRepositoryInterface $sectorRepository,
        PermissionRepositoryInterface $permissionRepository,
        SectorPermissionRepositoryInterface $sectorPermissionRepository
    ) {
        $this->sectorRepository = $sectorRepository;
        $this->permissionRepository = $permissionRepository;
        $this->sectorPermissionRepository = $sectorPermissionRepository;
    }

    public function insert(SectorPermission $new): SectorPermission
    {
        try {
            $this->sectorRepository->checkIfExists(
                Id::make($new->getSectorIdValue())
            );

            $this->permissionRepository->checkIfExists(
                Id::make($new->getPermissionIdValue())
            );

            $insertedSectorPermission = $this->sectorPermissionRepository->insert($new);

            return $insertedSectorPermission;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(SectorPermission $existant): bool
    {
        try {
            $this->sectorPermissionRepository->checkIfExists(
                Id::make($existant->getIdValue())
            );

            $this->sectorRepository->checkIfExists(
                Id::make($existant->getSectorIdValue())
            );

            $this->permissionRepository->checkIfExists(
                Id::make($existant->getPermissionIdValue())
            );

            $wasUpdated = $this->sectorPermissionRepository->update($existant);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(Id $id): bool
    {
        try {
            $this->sectorPermissionRepository->checkIfExists($id);

            $wasDeleted = $this->sectorPermissionRepository->delete($id);

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): SectorPermission
    {
        try {
            $fetchedSectorPermission = $this->sectorPermissionRepository->findById(
                $id
            );

            return $fetchedSectorPermission;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): SectorPermissionCollection
    {
        try {
            return $this->sectorPermissionRepository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
