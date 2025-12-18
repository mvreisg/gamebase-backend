<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Sector\Sector;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedNameException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockSectorRepository implements SectorRepositoryInterface
{
    private array $data;
    private int $idIndex;

    public function __construct()
    {
        $this->data = [];
        $this->idIndex = 0;
    }

    public function insert(Sector $sector): Sector
    {
        $this->idIndex++;
        $sector->setId($this->idIndex);
        $this->data[] = $sector;
        return new Sector(
            $sector->getId(),
            $sector->getName(),
            $sector->getIsActive()
        );
    }

    public function update(Sector $sector): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $sector->getId()) {
                $index = $key;
            }
        }

        if ($index < 0) {
            return false;
        }

        $foundSector = $this->data[$index];

        $hasDifferentNames =
            $foundSector->getName() !== $sector->getName();

        $hasDifferentIsActive =
            $foundSector->getIsActive() !== $sector->getIsActive();

        $isDifferent = $hasDifferentNames || $hasDifferentIsActive;

        if ($isDifferent === false) {
            return false;
        }

        $this->data[$index] = new Sector(
            $sector->getId(),
            $sector->getName(),
            $sector->getIsActive()
        );

        return true;
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $index = $key;
            }
        }

        if ($index < 0) {
            return false;
        }

        $foundSector = $this->data[$index];

        $wasUpdated = $foundSector->getIsActive() !== $isActive;

        if ($wasUpdated === false) {
            return false;
        }

        $this->data[$index]->setIsActive($isActive);

        return true;
    }

    public function findById(int $id): Sector
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant sector with id $id"
        );
    }

    public function findAll(): array
    {
        return $this->data;
    }

    public function checkIfExists(int $id): void
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant sector with id $id"
        );
    }

    public function checkDuplicatedNames(string $name): void
    {
        $array = array_filter(
            $this->data,
            fn (Sector $sector) => strcmp($sector->getName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedNameException(
                "Duplicated sector name: $name"
            );
        }
    }
}
