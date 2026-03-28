<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Genre;

use Mvreisg\GamebaseBackend\Domain\Entities\Genre;
use Mvreisg\GamebaseBackend\Domain\Entities\GenreCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GenreRepositoryInterface;

class GenreService
{
    private GenreRepositoryInterface $repository;

    public function __construct(GenreRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(Genre $genre): Genre
    {
        try {
            $this->repository->checkDuplicatedNames(
                $genre->getName()
            );

            $insertedGenre = $this->repository->insert($genre);

            return $insertedGenre;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(Genre $genre): bool
    {
        try {
            $this->repository->checkIfExists(
                $genre->getId()
            );

            $this->repository->checkDuplicatedNames(
                $genre->getName()
            );

            $wasUpdated = $this->repository->update($genre);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        try {
            $this->repository->checkIfExists($id);

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): Genre
    {
        try {
            $fetchedGenreEntity = $this->repository->findById(
                $id
            );

            return $fetchedGenreEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): GenreCollection
    {
        try {
            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
