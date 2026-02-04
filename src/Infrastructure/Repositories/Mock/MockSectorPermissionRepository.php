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
        $this->id = Id::make(1);
    }

    public function insert(SectorPermission $parameter): SectorPermission
    {
        $parameter->setId(
            Id::make(
                $this->id->getValue()
            )
        );
        $this->collection->add(
            $parameter
        );
        $this->id->increment(1);
        return $parameter;
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

        $new = new SectorPermission(
            Id::make($sectorPermission->getSectorIdValue()),
            Id::make($sectorPermission->getPermissionIdValue())
        );
        $new->setId(Id::make($sectorPermission->getIdValue()));

        $this->collection->replace(
            Id::make($sectorPermission->getIdValue()),
            $new
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
