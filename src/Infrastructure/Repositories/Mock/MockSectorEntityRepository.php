<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\SectorEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockDuplicatedEntryException;

class MockSectorEntityRepository implements SectorEntityRepositoryInterface
{
    private array $data;
    private int $index;

    public function __construct()
    {
        $this->data = [];
        $this->index = 0;
    }

    public function insert(SectorEntity $sectorEntity): SectorEntity
    {
        $this->index++;
        $sectorEntity->setId($this->index);
        $this->data[] = $sectorEntity;
        $newSectorEntity = new SectorEntity(
            $sectorEntity->getId(),
            $sectorEntity->getName(),
            $sectorEntity->getIsActive()
        );
        return $newSectorEntity;
    }

    public function update(SectorEntity $sectorEntity): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $sectorEntity->getId()) {
                $index = $key;
            }
        }

        if ($index < 0) {
            return false;
        }

        $this->data[$index] = new SectorEntity(
            $sectorEntity->getId(),
            $sectorEntity->getName(),
            $sectorEntity->getIsActive()
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

        $foundSectorEntity = $this->data[$index];

        $wasUpdated =
            $foundSectorEntity->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $wasUpdated;
    }

    public function findById(int $id): SectorEntity|null
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

    public function checkDuplicatedNames(string $name): void
    {
        $array = array_filter(
            $this->data,
            fn (SectorEntity $sectorEntity) => strcmp($sectorEntity->getName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedEntryException(
                $name
            );
        }
    }
}
