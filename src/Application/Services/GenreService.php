<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Application\Exceptions\Repositories\RepositoryException;
use Mvreisg\GamebaseBackend\Domain\Entities\GenreEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreEntityRepositoryInterface;

class GenreService
{
    private GenreEntityRepositoryInterface $repository;

    public function __construct(GenreEntityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(string $name, bool $isActive): GenreEntity
    {
        $genreEntity = new GenreEntity(
            PHP_INT_MAX,
            $name,
            $isActive
        );

        try {
            $genreEntity->validateName();

            $validatedName = $genreEntity->getName();

            $this->repository->checkDuplicatedNames($validatedName);

            $insertedGenreEntity = $this->repository->insert($genreEntity);

            return $insertedGenreEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(int $id, string $name, bool $isActive): bool
    {
        $genreEntity = new GenreEntity(
            $id,
            $name,
            $isActive
        );

        try {
            $genreEntity->validateId();
            $genreEntity->validateName();

            /*
            $validatedName = $genre->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome do gênero a ser atualizado já existe no repositório!'
                );
            }
            */

            $wasUpdated = $this->repository->update($genreEntity);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $genreEntity = new GenreEntity(
            $id,
            '',
            $isActive
        );

        try {
            $genreEntity->validateId();

            $validatedId = $genreEntity->getId();
            $validatedIsActive = $genreEntity->getIsActive();

            $wasUpdated = $this->repository->setIsActive(
                $validatedId,
                $validatedIsActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): GenreEntity|null
    {
        $genreEntity = new GenreEntity(
            $id
        );

        try {
            $genreEntity->validateId();

            $validatedId = $genreEntity->getId();

            $fetchedGenreEntity = $this->repository->findById(
                $validatedId
            );

            return $fetchedGenreEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $fetchedGenreEntities = $this->repository->findAll();

            return $fetchedGenreEntities;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
