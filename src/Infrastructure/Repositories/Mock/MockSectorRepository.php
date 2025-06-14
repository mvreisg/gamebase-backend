<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Sector;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorRepositoryInterface;

class MockSectorRepository implements SectorRepositoryInterface
{
    private array $data;
    private int $index;

    public function __construct()
    {
        $this->data = [];
        $this->index = 0;
    }

    public function insert(Sector $sector): Sector
    {
        $this->index++;
        $sector->setId($this->index);
        $this->data[] = $sector;
        $newSector = new Sector();
        $newSector->setId($sector->getId());
        $newSector->setName($sector->getName());
        $newSector->setIsActive($sector->getIsActive());
        return $newSector;
    }

    public function update(Sector $sector): bool
    {
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $sector->getId()) {
                $index = $key;
            }
        }

        if ($index === null) {
            return false;
        }

        $modifiedSector = $this->data[$index];

        $modifiedSector->setId($sector->getId());
        $modifiedSector->setName($sector->getName());
        $modifiedSector->setIsActive($sector->getIsActive());

        $this->data[$index] = $modifiedSector;

        return true;
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $index = $key;
            }
        }

        if ($index === null) {
            return false;
        }

        $foundSector = $this->data[$index];

        $changedSomething = $foundSector->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $changedSomething;
    }

    public function findById(int $id): Sector|null
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

    public function hasDuplicatedNames(string $name): bool
    {
        $array = array_filter($this->data, function (Sector $sector) use ($name) {
            return strcmp($sector->getName(), $name) === 0;
        });
        return count($array) > 0;
    }
}
