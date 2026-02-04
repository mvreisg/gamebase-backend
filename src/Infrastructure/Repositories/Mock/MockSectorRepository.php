<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Data\Sector;
use Mvreisg\GamebaseBackend\Domain\Data\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockSectorRepository implements SectorRepositoryInterface
{
    private SectorCollection $collection;
    private Id $id;

    public function __construct()
    {
        $this->collection = new SectorCollection(null);
        $this->id = Id::make(1);
    }

    public function insert(Sector $parameter): Sector
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

    public function update(Sector $sector): bool
    {
        $foundSector = $this->collection->findById(
            Id::make($sector->getIdValue())
        );

        if ($foundSector === null) {
            throw new MockUnexistantRegisterException(
                "id: {$sector->getIdValue()}"
            );
        }

        $hasDifferentNames =
            $foundSector->getNameValue() !== $sector->getNameValue();

        $hasDifferentIsActive =
            $foundSector->getIsActive() !== $sector->getIsActive();

        $isDifferent = $hasDifferentNames || $hasDifferentIsActive;

        if ($isDifferent === false) {
            return false;
        }

        $new = new Sector(
            Name::make($sector->getNameValue()),
            $sector->getIsActive()
        );
        $new->setId(Id::make($sector->getIdValue()));

        $this->collection->replace(
            Id::make($sector->getIdValue()),
            $new
        );
        return true;
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        $foundSector = $this->collection->findById(
            $id
        );

        if ($foundSector === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        $wasUpdated = $foundSector->getIsActive() !== $isActive;

        if ($wasUpdated === false) {
            return false;
        }

        $new = new Sector(
            Name::make($foundSector->getNameValue()),
            $isActive
        );
        $new->setId(Id::make($foundSector->getIdValue()));

        $this->collection->replace(
            Id::make($foundSector->getIdValue()),
            $new
        );
        return true;
    }

    public function findById(Id $id): Sector
    {
        $foundSector = $this->collection->findById(
            $id
        );

        if ($foundSector === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        return $foundSector;
    }

    public function findAll(): SectorCollection
    {
        return $this->collection;
    }

    public function checkIfExists(Id $id): void
    {
        $foundSector = $this->collection->findById(
            $id
        );

        if ($foundSector === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }
    }

    public function checkDuplicatedNames(Name $name): void
    {
        $foundSectors = $this->collection->findByName(
            $name
        );

        if ($foundSectors->count() > 1) {
            throw new MockDuplicatedRegisterException(
                "name: {$name->getValue()}"
            );
        }
    }
}
