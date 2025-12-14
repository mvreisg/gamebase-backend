<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\SectorPermission;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorPermissionInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockSectorPermissionRepository implements SectorPermissionInterface
{
    private array $data;
    private int $id;
    private SectorRepositoryInterface $sectorEntityRepository;
    private PermissionRepositoryInterface $permissionEntityRepository;

    public function __construct(
        SectorRepositoryInterface $sectorEntityRepository,
        PermissionRepositoryInterface $permissionEntityRepository
    ) {
        $this->data = [];
        $this->id = 0;
        $this->sectorEntityRepository = $sectorEntityRepository;
        $this->permissionEntityRepository = $permissionEntityRepository;
    }

    public function insert(SectorPermission $sectorPermission): SectorPermission
    {
        try {
            $sectorId = $sectorPermission->getSectorId();
            $user = $this->sectorEntityRepository->findById($sectorId);
            if ($user === null) {
                throw new MockUnexistantRegisterException(
                    "sectorId: $sectorId"
                );
            }

            $permissionId = $sectorPermission->getPermissionId();
            $permission = $this->permissionEntityRepository->findById($permissionId);
            if ($permission === null) {
                throw new MockUnexistantRegisterException(
                    "permissionId: $permissionId"
                );
            }

            $this->id++;
            $id = $this->id;

            $sectorPermission->setId($id);
            $this->data[] = $sectorPermission;
            $newsectorPermissionEntity = new SectorPermission(
                $id,
                $sectorId,
                $permissionId
            );
            return $newsectorPermissionEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(SectorPermission $sectorPermission): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $sectorPermission->getId()) {
                $index = $key;
                break;
            }
        }

        if ($index < 0) {
            return false;
        }

        $sectorPermissionEntityToBeModified = $this->data[$index];

        $hasDifferentSectorId =
            $sectorPermissionEntityToBeModified->getSectorId() !== $sectorPermission->getSectorId();

        $hasDifferentPermissionId =
            $sectorPermissionEntityToBeModified->getPermissionId() !== $sectorPermission->getPermissionId();

        $wasUpdated = $hasDifferentSectorId || $hasDifferentPermissionId;

        $this->data[$index] = new SectorPermission(
            $sectorPermission->getId(),
            $sectorPermission->getSectorId(),
            $sectorPermission->getPermissionId()
        );

        return $wasUpdated;
    }

    public function delete(SectorPermission $sectorPermission): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $sectorPermission->getId()) {
                $index = $key;
                break;
            }
        }

        if ($index < 0) {
            return false;
        }

        unset($this->data[$index]);
        return true;
    }

    /*
    public function setIsActive(int $id, bool $isActive): bool
    {
        $idToSet = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $idToSet = $key;
            }
        }

        if ($idToSet === null) {
            return false;
        }

        $findedsectorPermissionEntity = $this->data[$idToSet];

        $changedSomething = $findedsectorPermissionEntity->getIsActive() !== $isActive;

        if ($changedSomething) {
            $this->data[$idToSet]->setIsActive($isActive);
            return true;
        }

        return false;
    }
    */

    public function findById(int $id): SectorPermission
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        return null;
    }

    public function findAll(): array
    {
        return $this->data;
    }
}
