<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Genre\Genre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedEntryException;

class MockGenreRepository implements GenreRepositoryInterface
{
    private array $data;
    private int $index;

    public function __construct()
    {
        $this->data = [];
        $this->index = 0;
    }

    public function insert(Genre $genre): Genre
    {
        $this->index++;
        $genre->setId($this->index);
        $this->data[] = $genre;
        $newGenreEntity = new Genre(
            $genre->getId(),
            $genre->getName() .
            $genre->getIsActive()
        );
        return $newGenreEntity;
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

        $foundGenreEntity = $this->data[$index];

        $wasUpdated =
            $foundGenreEntity->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $wasUpdated;
    }

    public function findById(int $id): Genre
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

    public function checkDuplicatedNames(string $name): void
    {
        $array = array_filter(
            $this->data,
            fn (Genre $genre) => strcmp($genre->getName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedEntryException(
                $name
            );
        }
    }
}
