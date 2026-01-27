<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Data\Platform;
use Mvreisg\GamebaseBackend\Domain\Data\PlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockPlatformRepository implements PlatformRepositoryInterface
{
    private PlatformCollection $collection;
    private Id $id;

    public function __construct()
    {
        $this->collection = new PlatformCollection();
        $this->id = new Id(0);
    }

    public function insert(Platform $platform): Platform
    {
        $this->id->increment(1);
        $platform = new Platform(
            new Id($this->id->getValue()),
            new Name($platform->getNameValue()),
            $platform->getIsActive()
        );
        $this->collection->add($platform);
        return $platform;
    }

    public function update(Platform $platform): bool
    {
        $foundPlatform = $this->collection->findById(
            Id::make($platform->getIdValue())
        );

        if ($foundPlatform === null) {
            throw new MockUnexistantRegisterException(
                "id: {$platform->getIdValue()}"
            );
        }

        $hasDifferentNames =
            $foundPlatform->getNameValue() !== $platform->getNameValue();

        $hasDifferentIsActive =
            $foundPlatform->getIsActive() !== $platform->getIsActive();

        $isDifferent = $hasDifferentNames || $hasDifferentIsActive;

        if ($isDifferent === false) {
            return false;
        }

        $this->collection->replace(
            Id::make($platform->getIdValue()),
            new Platform(
                Id::make($platform->getIdValue()),
                Name::make($platform->getNameValue()),
                $platform->getIsActive()
            )
        );
        return true;
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        $foundPlatform = $this->collection->findById(
            $id
        );

        if ($foundPlatform === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        $wasUpdated = $foundPlatform->getIsActive() !== $isActive;

        if ($wasUpdated === false) {
            return false;
        }
        $this->collection->replace(
            $id,
            new Platform(
                Id::make($foundPlatform->getIdValue()),
                Name::make($foundPlatform->getNameValue()),
                $isActive
            )
        );
        return true;
    }

    public function findById(Id $id): Platform
    {
        $foundPlatform = $this->collection->findById(
            $id
        );

        if ($foundPlatform === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        return $foundPlatform;
    }

    public function findAll(): PlatformCollection
    {
        return $this->collection;
    }

    public function checkIfExists(Id $id): void
    {
        $foundPlatform = $this->collection->findById(
            $id
        );

        if ($foundPlatform === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }
    }

    public function checkDuplicatedNames(Name $name): void
    {
        $foundPlatforms = $this->collection->findByName(
            $name
        );

        if ($foundPlatforms->count() > 1) {
            throw new MockDuplicatedRegisterException(
                "name: {$name->getValue()}"
            );
        }
    }
}
