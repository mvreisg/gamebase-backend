<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Platform;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedEntryException;

class MockPlatformRepository implements PlatformRepositoryInterface
{
    private array $data;
    private int $index;

    public function __construct()
    {
        $this->data = [];
        $this->index = 0;
    }

    public function insert(Platform $platform): Platform
    {
        $this->index++;
        $platform->setId($this->index);
        $this->data[] = $platform;
        $newPlatformEntity = new Platform(
            $platform->getId(),
            $platform->getName(),
            $platform->getIsActive()
        );
        return $newPlatformEntity;
    }

    public function update(Platform $platform): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $platform->getId()) {
                $index = $key;
            }
        }

        if ($index < 0) {
            return false;
        }

        $this->data[$index] = new Platform(
            $platform->getId(),
            $platform->getName(),
            $platform->getIsActive()
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

    public function findById(int $id): Platform
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
            fn (Platform $platform) => strcmp($platform->getName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedEntryException(
                $name
            );
        }
    }
}
