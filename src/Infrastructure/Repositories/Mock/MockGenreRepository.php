<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Data\Genre;
use Mvreisg\GamebaseBackend\Domain\Data\GenreCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockGenreRepository implements GenreRepositoryInterface
{
    private GenreCollection $collection;
    private Id $id;

    public function __construct()
    {
        $this->collection = new GenreCollection();
        $this->id = Id::make(1);
    }

    public function insert(Genre $parameter): Genre
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

    public function update(Genre $genre): bool
    {
        $foundGenre = $this->collection->findById(
            Id::make($genre->getIdValue())
        );

        if ($foundGenre === null) {
            throw new MockUnexistantRegisterException(
                "id: {$genre->getIdValue()}"
            );
        }

        $hasDifferentNames =
            $foundGenre->getNameValue() !== $genre->getNameValue();

        $hasDifferentIsActive =
            $foundGenre->getIsActive() !== $genre->getIsActive();

        $isDifferent = $hasDifferentNames || $hasDifferentIsActive;

        if ($isDifferent === false) {
            return false;
        }

        $new = new Genre(
            Name::make($genre->getNameValue()),
            $genre->getIsActive()
        );
        $new->setId(Id::make($genre->getIdValue()));

        $this->collection->replace(
            Id::make($genre->getIdValue()),
            $new
        );
        return true;
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        $foundGenre = $this->collection->findById(
            $id
        );

        if ($foundGenre === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        $wasUpdated = $foundGenre->getIsActive() !== $isActive;

        if ($wasUpdated === false) {
            return false;
        }

        $new = new Genre(
            Name::make($foundGenre->getNameValue()),
            $isActive
        );
        $new->setId(Id::make($foundGenre->getIdValue()));

        $this->collection->replace(
            Id::make($foundGenre->getIdValue()),
            $new
        );
        return true;
    }

    public function findById(Id $id): Genre
    {
        $foundGenre = $this->collection->findById(
            $id
        );

        if ($foundGenre === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        return $foundGenre;
    }

    public function findAll(): GenreCollection
    {
        return $this->collection;
    }

    public function checkIfExists(Id $id): void
    {
        $foundGenre = $this->collection->findById(
            $id
        );

        if ($foundGenre === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }
    }

    public function checkDuplicatedNames(Name $name): void
    {
        $foundGenres = $this->collection->findByName(
            $name
        );

        if ($foundGenres->count() > 1) {
            throw new MockDuplicatedRegisterException(
                "name: {$name->getValue()}"
            );
        }
    }
}
