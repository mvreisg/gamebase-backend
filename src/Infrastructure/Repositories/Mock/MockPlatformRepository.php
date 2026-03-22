<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Platform;
use Mvreisg\GamebaseBackend\Domain\Entities\PlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
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
        $this->id = Id::make(1);
    }

    public function insert(Platform $parameter): Platform
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

        $new = new Platform(
            Name::make($platform->getNameValue()),
            $platform->getIsActive()
        );
        $new->setId(Id::make($platform->getIdValue()));

        $this->collection->replace(
            Id::make($platform->getIdValue()),
            $new
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

        $new = new Platform(
            Name::make($foundPlatform->getNameValue()),
            $isActive
        );
        $new->setId(Id::make($foundPlatform->getIdValue()));

        $this->collection->replace(
            Id::make($foundPlatform->getIdValue()),
            $new
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
