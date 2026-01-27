<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermission;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockSectorPermissionRepository implements SectorPermissionRepositoryInterface
{
    private SectorPermissionCollection $collection;
    private Id $id;

    public function __construct()
    {
        $this->collection = new SectorPermissionCollection();
        $this->id = new Id(0);
    }

    public function insert(SectorPermission $sectorPermission): SectorPermission
    {
        $this->id->increment(1);
        $newSectorPermission = new SectorPermission(
            Id::make($sectorPermission->getIdValue()),
            Id::make($sectorPermission->getSectorIdValue()),
            Id::make($sectorPermission->getPermissionIdValue())
        );
        $this->collection->add($newSectorPermission);
        return $newSectorPermission;
    }

    public function update(SectorPermission $sectorPermission): bool
    {
        $foundSectorPermission = $this->collection->findById(
            Id::make($sectorPermission->getIdValue())
        );

        if ($foundSectorPermission === null) {
            throw new MockUnexistantRegisterException(
                "id: {$sectorPermission->getIdValue()}"
            );
        }

        $hasDifferentSectorId =
            $foundSectorPermission->getSectorIdValue() !== $sectorPermission->getSectorIdValue();

        $hasDifferentPermissionId =
            $foundSectorPermission->getPermissionIdValue() !== $sectorPermission->getPermissionIdValue();

        $isDifferent = $hasDifferentSectorId || $hasDifferentPermissionId;

        if ($isDifferent === false) {
            return false;
        }

        $this->collection->replace(
            Id::make($sectorPermission->getIdValue()),
            new SectorPermission(
                Id::make($sectorPermission->getIdValue()),
                Id::make($sectorPermission->getSectorIdValue()),
                Id::make($sectorPermission->getPermissionIdValue())
            )
        );
        return true;
    }

    public function delete(Id $id): bool
    {
        return $this->collection->remove(
            $id
        );
    }

    public function findById(Id $id): SectorPermission
    {
        $foundSectorPermission = $this->collection->findById(
            $id
        );

        if ($foundSectorPermission === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        return $foundSectorPermission;
    }

    public function findAllByPermissionId(Id $permissionId): SectorPermissionCollection
    {
        return $this->collection->findAllByPermissionId($permissionId);
    }

    public function findAll(): SectorPermissionCollection
    {
        return $this->collection;
    }

    public function checkIfExists(Id $id): void
    {
        $foundSectorPermission = $this->collection->findById(
            $id
        );

        if ($foundSectorPermission === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }
    }
}
