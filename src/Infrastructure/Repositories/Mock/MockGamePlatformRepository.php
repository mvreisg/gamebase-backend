<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Data\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Data\GamePlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockGamePlatformRepository implements GamePlatformRepositoryInterface
{
    private GamePlatformCollection $collection;
    private Id $id;

    public function __construct()
    {
        $this->collection = new GamePlatformCollection();
        $this->id = Id::make(0);
    }

    public function insert(GamePlatform $gamePlatform): GamePlatform
    {
        $this->id->increment(1);
        $newGamePlatform = new GamePlatform(
            Id::make($this->id->getValue()),
            Id::make($gamePlatform->getPlatformIdValue()),
            Id::make($gamePlatform->getGameIdValue())
        );
        $this->collection->add($newGamePlatform);
        return $newGamePlatform;
    }

    public function update(GamePlatform $gamePlatform): bool
    {
        $foundGamePlatform = $this->collection->findById(
            Id::make($gamePlatform->getIdValue())
        );

        if ($foundGamePlatform === null) {
            throw new MockUnexistantRegisterException(
                "id: {$gamePlatform->getIdValue()}"
            );
        }

        $hasDifferentGameId =
            $foundGamePlatform->getGameIdValue() !== $gamePlatform->getGameIdValue();

        $hasDifferentPlatformId =
            $foundGamePlatform->getPlatformIdValue() !== $gamePlatform->getPlatformIdValue();

        $isDifferent = $hasDifferentGameId || $hasDifferentPlatformId;

        if ($isDifferent === false) {
            return false;
        }

        $this->collection->replace(
            Id::make($gamePlatform->getIdValue()),
            new GamePlatform(
                Id::make($gamePlatform->getIdValue()),
                Id::make($gamePlatform->getPlatformIdValue()),
                Id::make($gamePlatform->getGameIdValue())
            )
        );
        return true;
    }

    public function delete(Id $id): bool
    {
        return $this->collection->remove(
            $id
        );
    }

    public function findById(Id $id): GamePlatform
    {
        $foundGamePlatform = $this->collection->findById(
            $id
        );

        if ($foundGamePlatform === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        return $foundGamePlatform;
    }

    public function findAll(): GamePlatformCollection
    {
        return $this->collection;
    }

    public function checkIfExists(Id $id): void
    {
        $foundGamePlatform = $this->collection->findById(
            $id
        );

        if ($foundGamePlatform === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }
    }
}
