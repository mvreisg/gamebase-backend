<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\GenreEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockDuplicatedEntryException;

class MockGenreEntityRepository implements GenreEntityRepositoryInterface
{
    private array $data;
    private int $index;

    public function __construct()
    {
        $this->data = [];
        $this->index = 0;
    }

    public function insert(GenreEntity $genreEntity): GenreEntity
    {
        $this->index++;
        $genreEntity->setId($this->index);
        $this->data[] = $genreEntity;
        $newGenreEntity = new GenreEntity(
            $genreEntity->getId(),
            $genreEntity->getName() .
            $genreEntity->getIsActive()
        );
        return $newGenreEntity;
    }

    public function update(GenreEntity $genreEntity): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $genreEntity->getId()) {
                $index = $key;
            }
        }

        if ($index < 0) {
            return false;
        }

        $this->data[$index] = new GenreEntity(
            $genreEntity->getId(),
            $genreEntity->getName(),
            $genreEntity->getIsActive()
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

    public function findById(int $id): GenreEntity|null
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
            fn (GenreEntity $genreEntity) => strcmp($genreEntity->getName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedEntryException(
                $name
            );
        }
    }
}
