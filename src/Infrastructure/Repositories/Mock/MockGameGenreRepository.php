<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenreCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockGameGenreRepository implements GameGenreRepositoryInterface
{
    private GameGenreCollection $collection;
    private Id $id;

    public function __construct()
    {
        $this->collection = new GameGenreCollection();
        $this->id = Id::make(1);
    }

    public function insert(GameGenre $parameter): GameGenre
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

    public function update(GameGenre $gameGenre): bool
    {
        $foundGameGenre = $this->collection->findById(
            Id::make($gameGenre->getIdValue())
        );

        if ($foundGameGenre === null) {
            throw new MockUnexistantRegisterException(
                "id: {$gameGenre->getIdValue()}"
            );
        }

        $hasDifferentGameId =
            $foundGameGenre->getGameIdValue() !== $gameGenre->getGameIdValue();

        $hasDifferentGenreId =
            $foundGameGenre->getGenreIdValue() !== $gameGenre->getGenreIdValue();

        $isDifferent = $hasDifferentGameId || $hasDifferentGenreId;

        if ($isDifferent === false) {
            return false;
        }

        $new = new GameGenre(
            Id::make($gameGenre->getGameIdValue()),
            Id::make($gameGenre->getGenreIdValue())
        );
        $new->setId(Id::make($gameGenre->getIdValue()));

        $this->collection->replace(
            Id::make($gameGenre->getIdValue()),
            $new
        );

        return true;
    }

    public function delete(Id $id): bool
    {
        return $this->collection->remove(
            $id
        );
    }

    public function findById(Id $id): GameGenre
    {
        $foundGameGenre = $this->collection->findById(
            $id
        );

        if ($foundGameGenre === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        return $foundGameGenre;
    }

    public function findAll(): GameGenreCollection
    {
        return $this->collection;
    }

    public function checkIfExists(Id $id): void
    {
        $foundGameGenre = $this->collection->findById(
            $id
        );

        if ($foundGameGenre === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }
    }
}
