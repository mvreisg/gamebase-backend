<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\SectorPermissionEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorPermissionEntityRepositoryInterface;

class SectorPermissionService
{
    private SectorPermissionEntityRepositoryInterface $repository;

    public function __construct(SectorPermissionEntityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(int $sectorId, int $permissionId): SectorPermissionEntity
    {
        $sectorPermissionEntity = new SectorPermissionEntity(
            PHP_INT_MAX,
            $sectorId,
            $permissionId
        );

        try {
            $sectorPermissionEntity->validateSectorId();
            $sectorPermissionEntity->validatePermissionId();

            $insertedSectorPermissionEntity = $this->repository->insert($sectorPermissionEntity);

            return $insertedSectorPermissionEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(int $id, int $sectorId, int $permissionId): bool
    {
        $sectorPermissionEntity = new SectorPermissionEntity(
            $id,
            $sectorId,
            $permissionId
        );

        try {
            $sectorPermissionEntity->validateId();
            $sectorPermissionEntity->validateSectorId();
            $sectorPermissionEntity->validatePermissionId();

            $wasUpdated = $this->repository->update($sectorPermissionEntity);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $sectorPermissionEntity = new SectorPermissionEntity(
            $id
        );

        try {
            $sectorPermissionEntity->validateId();

            $wasDeleted = $this->repository->delete($sectorPermissionEntity);

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): SectorPermissionEntity|null
    {
        $sectorPermissionEntity = new SectorPermissionEntity(
            $id
        );

        try {
            $sectorPermissionEntity->validateId();

            $validatedId = $sectorPermissionEntity->getId();

            $fetchedSectorPermissionEntity = $this->repository->findById($validatedId);

            return $fetchedSectorPermissionEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $fetchedSectorPermissionEntities = $this->repository->findAll();

            return $fetchedSectorPermissionEntities;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
