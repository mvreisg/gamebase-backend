<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Genre\Genre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedNameException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockGenreRepository implements GenreRepositoryInterface
{
    private array $data;
    private int $idIndex;

    public function __construct()
    {
        $this->data = [];
        $this->idIndex = 0;
    }

    public function insert(Genre $genre): Genre
    {
        $this->idIndex++;
        $genre->setId($this->idIndex);
        $this->data[] = $genre;
        return new Genre(
            $genre->getId(),
            $genre->getName() .
            $genre->getIsActive()
        );
    }

    public function update(Genre $genre): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $genre->getId()) {
                $index = $key;
            }
        }

        if ($index < 0) {
            return false;
        }

        $foundGenre = $this->data[$index];

        $hasDifferentNames =
            $foundGenre->getName() !== $genre->getName();

        $hasDifferentIsActive =
            $foundGenre->getIsActive() !== $genre->getIsActive();

        $isDifferent = $hasDifferentNames || $hasDifferentIsActive;

        if ($isDifferent === false) {
            return false;
        }

        $this->data[$index] = new Genre(
            $genre->getId(),
            $genre->getName(),
            $genre->getIsActive()
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

        $foundGenre = $this->data[$index];

        $wasUpdated = $foundGenre->getIsActive() !== $isActive;

        if ($wasUpdated === false) {
            return false;
        }

        $this->data[$index]->setIsActive($isActive);

        return true;
    }

    public function findById(int $id): Genre
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant game with id $id"
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
            "Unexistant genre with id $id"
        );
    }

    public function checkDuplicatedNames(string $name): void
    {
        $array = array_filter(
            $this->data,
            fn (Genre $genre) => strcmp($genre->getName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedNameException(
                $name
            );
        }
    }
}
