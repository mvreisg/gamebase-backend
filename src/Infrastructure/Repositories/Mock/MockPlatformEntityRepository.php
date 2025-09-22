<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\PlatformEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockDuplicatedEntryException;

class MockPlatformEntityRepository implements PlatformEntityRepositoryInterface
{
    private array $data;
    private int $index;

    public function __construct()
    {
        $this->data = [];
        $this->index = 0;
    }

    public function insert(PlatformEntity $platformEntity): PlatformEntity
    {
        $this->index++;
        $platformEntity->setId($this->index);
        $this->data[] = $platformEntity;
        $newPlatformEntity = new PlatformEntity(
            $platformEntity->getId(),
            $platformEntity->getName(),
            $platformEntity->getIsActive()
        );
        return $newPlatformEntity;
    }

    public function update(PlatformEntity $platformEntity): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $platformEntity->getId()) {
                $index = $key;
            }
        }

        if ($index < 0) {
            return false;
        }

        $this->data[$index] = new PlatformEntity(
            $platformEntity->getId(),
            $platformEntity->getName(),
            $platformEntity->getIsActive()
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

        $foundPlatformEntity = $this->data[$index];

        $wasUpdated =
            $foundPlatformEntity->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $wasUpdated;
    }

    public function findById(int $id): PlatformEntity|null
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
            fn (PlatformEntity $platformEntity) => strcmp($platformEntity->getName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedEntryException(
                $name
            );
        }
    }
}
