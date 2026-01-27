<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Data\Game;
use Mvreisg\GamebaseBackend\Domain\Data\GameCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockGameRepository implements GameRepositoryInterface
{
    private GameCollection $collection;
    private Id $id;

    public function __construct()
    {
        $this->collection = new GameCollection();
        $this->id = Id::make(0);
    }

    public function insert(Game $game): Game
    {
        $this->id->increment(1);
        $newGame = new Game(
            Id::make($this->id->getValue()),
            new Name($game->getNameValue()),
            $game->getIsActive()
        );
        $this->collection->add($newGame);
        return $newGame;
    }

    public function update(Game $game): bool
    {
        $foundGame = $this->collection->findById(
            Id::make($game->getIdValue())
        );

        if ($foundGame === null) {
            throw new MockUnexistantRegisterException(
                "id: {$game->getIdValue()}"
            );
        }

        $hasDifferentNames =
            $foundGame->getNameValue() !== $game->getNameValue();

        $hasDifferentIsActive =
            $foundGame->getIsActive() !== $game->getIsActive();

        $isDifferent = $hasDifferentNames || $hasDifferentIsActive;

        if ($isDifferent === false) {
            return false;
        }

        $this->collection->replace(
            Id::make($game->getIdValue()),
            new Game(
                Id::make($game->getIdValue()),
                Name::make($game->getNameValue()),
                $game->getIsActive()
            )
        );
        return true;
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        $foundGame = $this->collection->findById(
            $id
        );

        if ($foundGame === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        $wasUpdated = $foundGame->getIsActive() !== $isActive;

        if ($wasUpdated === false) {
            return false;
        }

        $this->collection->replace(
            $id,
            new Game(
                Id::make($foundGame->getIdValue()),
                Name::make($foundGame->getNameValue()),
                $isActive
            )
        );
        return true;
    }

    public function findById(Id $id): Game
    {
        $foundGame = $this->collection->findById(
            $id
        );

        if ($foundGame === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        return $foundGame;
    }

    public function findAll(): GameCollection
    {
        return $this->collection;
    }

    public function checkIfExists(Id $id): void
    {
        $foundGame = $this->collection->findById(
            $id
        );

        if ($foundGame === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }
    }

    public function checkDuplicatedNames(Name $name): void
    {
        $foundGames = $this->collection->findByName(
            $name
        );

        if ($foundGames->count() > 1) {
            throw new MockDuplicatedRegisterException(
                "name: {$name->getValue()}"
            );
        }
    }
}
