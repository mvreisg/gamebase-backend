<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Sector;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;

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
            throw new RepositoryUnexistantRegisterException(
                "id: {$sector->getIdValue()}"
            );
        }

        $hasDifferentNames =
            $foundSector->getNameValue() !== $sector->getNameValue();

        $hasDifferentValues =
            $foundSector->getSectorValue() !== $sector->getSectorValue();

        $hasDifferentIsActive =
            $foundSector->getIsActive() !== $sector->getIsActive();

        $isDifferent = $hasDifferentNames || $hasDifferentValues || $hasDifferentIsActive;

        if ($isDifferent === false) {
            return false;
        }

        $new = new Sector(
            Name::make($sector->getNameValue()),
            SectorValue::make($sector->getSectorValue()),
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
            throw new RepositoryUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        $wasUpdated = $foundSector->getIsActive() !== $isActive;

        if ($wasUpdated === false) {
            return false;
        }

        $new = new Sector(
            Name::make($foundSector->getNameValue()),
            SectorValue::make($foundSector->getSectorValue()),
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
            throw new RepositoryUnexistantRegisterException(
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
            throw new RepositoryUnexistantRegisterException(
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
            throw new RepositoryDuplicatedRegisterException(
                "name: {$name->getValue()}"
            );
        }
    }
}
