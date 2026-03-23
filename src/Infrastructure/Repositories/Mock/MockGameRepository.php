<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Game;
use Mvreisg\GamebaseBackend\Domain\Entities\GameCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GameRepositoryInterface;

class MockGameRepository implements GameRepositoryInterface
{
    private GameCollection $collection;
    private Id $id;

    public function __construct()
    {
        $this->collection = new GameCollection();
        $this->id = Id::make(1);
    }

    public function insert(Game $parameter): Game
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

    public function update(Game $game): bool
    {
        $foundGame = $this->collection->findById(
            Id::make($game->getIdValue())
        );

        if ($foundGame === null) {
            throw new RepositoryUnexistantRegisterException(
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

        $new = new Game(
            Name::make($game->getNameValue()),
            $game->getIsActive()
        );
        $new->setId(Id::make($game->getIdValue()));

        $this->collection->replace(
            Id::make($game->getIdValue()),
            $new
        );
        return true;
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        $foundGame = $this->collection->findById(
            $id
        );

        if ($foundGame === null) {
            throw new RepositoryUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        $wasUpdated = $foundGame->getIsActive() !== $isActive;

        if ($wasUpdated === false) {
            return false;
        }

        $new = new Game(
            Name::make($foundGame->getNameValue()),
            $isActive
        );
        $new->setId(Id::make($foundGame->getIdValue()));

        $this->collection->replace(
            Id::make($foundGame->getIdValue()),
            $new
        );
        return true;
    }

    public function findById(Id $id): Game
    {
        $foundGame = $this->collection->findById(
            $id
        );

        if ($foundGame === null) {
            throw new RepositoryUnexistantRegisterException(
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
            throw new RepositoryUnexistantRegisterException(
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
            throw new RepositoryDuplicatedRegisterException(
                "name: {$name->getValue()}"
            );
        }
    }
}
