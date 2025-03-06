<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Genre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseUnexistantValueException;

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
        $newGenre = new Genre();
        $newGenre->setId($genre->getId());
        $newGenre->setName($genre->getName());
        $newGenre->setIsActive($genre->getIsActive());
        return $newGenre;
    }

    public function update(Genre $genre): bool
    {
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $genre->getId()) {
                $index = $key;
            }
        }

        if ($index === null) {
            throw new DatabaseUnexistantValueException(
                'O gênero com o id ' . $genre->getId() . ' não existe no repositório!'
            );
        }

        $modifiedGenre = $this->data[$index];

        $modifiedGenre->setId($genre->getId());
        $modifiedGenre->setName($genre->getName());
        $modifiedGenre->setIsActive($genre->getIsActive());

        $this->data[$index] = $modifiedGenre;

        return true;
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $index = $key;
            }
        }

        if ($index === null) {
            throw new DatabaseUnexistantValueException(
                'O gênero com o id ' . $id . ' não existe no repositório!'
            );
        }

        $findedGenre = $this->data[$index];

        $changedSomething = $findedGenre->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $changedSomething;
    }

    public function findById(int $id): Genre|null
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

    public function hasDuplicatedNames(string $name): bool
    {
        $array = array_filter($this->data, function (Genre $genre) use ($name) {
            return strcmp($genre->getName(), $name) === 0;
        });
        return count($array) > 0;
    }
}
