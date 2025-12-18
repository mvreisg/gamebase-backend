<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Platform\Platform;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedNameException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockPlatformRepository implements PlatformRepositoryInterface
{
    private array $data;
    private int $idIndex;

    public function __construct()
    {
        $this->data = [];
        $this->idIndex = 0;
    }

    public function insert(Platform $platform): Platform
    {
        $this->idIndex++;
        $platform->setId($this->idIndex);
        $this->data[] = $platform;
        return new Platform(
            $platform->getId(),
            $platform->getName(),
            $platform->getIsActive()
        );
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

        $foundPlatform = $this->data[$index];

        $hasDifferentNames =
            $foundPlatform->getName() !== $platform->getName();

        $hasDifferentIsActive =
            $foundPlatform->getIsActive() !== $platform->getIsActive();

        $isDifferent = $hasDifferentNames || $hasDifferentIsActive;

        if ($isDifferent === false) {
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

        $foundPermission = $this->data[$index];

        $wasUpdated = $foundPermission->getIsActive() !== $isActive;

        if ($wasUpdated === false) {
            return false;
        }

        $this->data[$index]->setIsActive($isActive);

        return true;
    }

    public function findById(int $id): Platform
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant platform with id $id"
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
            "Unexistant platform with id $id"
        );
    }

    public function checkDuplicatedNames(string $name): void
    {
        $array = array_filter(
            $this->data,
            fn (Platform $platform) => strcmp($platform->getName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedNameException(
                "Duplicated platform name: $name"
            );
        }
    }
}
