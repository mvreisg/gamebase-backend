<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\SectorPermissionEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorPermissionEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockUnexistantRegisterException;

class MockSectorPermissionEntityRepository implements SectorPermissionEntityRepositoryInterface
{
    private array $data;
    private int $id;
    private SectorEntityRepositoryInterface $sectorEntityRepository;
    private PermissionEntityRepositoryInterface $permissionEntityRepository;

    public function __construct(
        SectorEntityRepositoryInterface $sectorEntityRepository,
        PermissionEntityRepositoryInterface $permissionEntityRepository
    ) {
        $this->data = [];
        $this->id = 0;
        $this->sectorEntityRepository = $sectorEntityRepository;
        $this->permissionEntityRepository = $permissionEntityRepository;
    }

    public function insert(SectorPermissionEntity $sectorPermissionEntity): SectorPermissionEntity
    {
        try {
            $sectorId = $sectorPermissionEntity->getSectorId();
            $user = $this->sectorEntityRepository->findById($sectorId);
            if ($user === null) {
                throw new MockUnexistantRegisterException(
                    $sectorId
                );
            }

            $permissionId = $sectorPermissionEntity->getPermissionId();
            $permission = $this->permissionEntityRepository->findById($permissionId);
            if ($permission === null) {
                throw new MockUnexistantRegisterException(
                    $permissionId
                );
            }

            $this->id++;
            $id = $this->id;

            $sectorPermissionEntity->setId($id);
            $this->data[] = $sectorPermissionEntity;
            $newsectorPermissionEntity = new SectorPermissionEntity(
                $id,
                $sectorId,
                $permissionId
            );
            return $newsectorPermissionEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(SectorPermissionEntity $sectorPermissionEntity): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $sectorPermissionEntity->getId()) {
                $index = $key;
                break;
            }
        }

        if ($index < 0) {
            return false;
        }

        $sectorPermissionEntityToBeModified = $this->data[$index];

        $hasDifferentSectorId =
            $sectorPermissionEntityToBeModified->getSectorId() !== $sectorPermissionEntity->getSectorId();

        $hasDifferentPermissionId =
            $sectorPermissionEntityToBeModified->getPermissionId() !== $sectorPermissionEntity->getPermissionId();

        $wasUpdated = $hasDifferentSectorId || $hasDifferentPermissionId;

        $this->data[$index] = new SectorPermissionEntity(
            $sectorPermissionEntity->getId(),
            $sectorPermissionEntity->getSectorId(),
            $sectorPermissionEntity->getPermissionId()
        );

        return $wasUpdated;
    }

    public function delete(SectorPermissionEntity $sectorPermissionEntity): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $sectorPermissionEntity->getId()) {
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

    public function findById(int $id): SectorPermissionEntity|null
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
