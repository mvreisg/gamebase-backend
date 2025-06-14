<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Platform;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformRepositoryInterface;

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
        $newPlatform = new Platform();
        $newPlatform->setId($platform->getId());
        $newPlatform->setName($platform->getName());
        $newPlatform->setIsActive($platform->getIsActive());
        return $newPlatform;
    }

    public function update(Platform $platform): bool
    {
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $platform->getId()) {
                $index = $key;
            }
        }

        if ($index === null) {
            return false;
        }

        $modifiedPlatform = $this->data[$index];

        $modifiedPlatform->setId($platform->getId());
        $modifiedPlatform->setName($platform->getName());
        $modifiedPlatform->setIsActive($platform->getIsActive());

        $this->data[$index] = $modifiedPlatform;

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

        $findedPlatform = $this->data[$index];

        $changedSomething = $findedPlatform->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $changedSomething;
    }

    public function findById(int $id): Platform|null
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
        $array = array_filter($this->data, function (Platform $platform) use ($name) {
            return strcmp($platform->getName(), $name) === 0;
        });
        return count($array) > 0;
    }
}
