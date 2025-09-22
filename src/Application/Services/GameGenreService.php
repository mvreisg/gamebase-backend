<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenreEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreEntityRepositoryInterface;

class GameGenreService
{
    private GameGenreEntityRepositoryInterface $repository;

    public function __construct(GameGenreEntityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(int $genreId, int $gameId): GameGenreEntity
    {
        $gameGenreEntity = new GameGenreEntity(
            PHP_INT_MAX,
            $genreId,
            $gameId
        );

        try {
            $gameGenreEntity->validateGenreId();
            $gameGenreEntity->validateGameId();

            $insertedGameGenreEntity = $this->repository->insert($gameGenreEntity);

            return $insertedGameGenreEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(int $id, int $genreId, int $gameId): bool
    {
        $gameGenreEntity = new GameGenreEntity(
            $id,
            $genreId,
            $gameId
        );

        try {
            $gameGenreEntity->validateId();
            $gameGenreEntity->validateGenreId();
            $gameGenreEntity->validateGameId();

            $wasUpdated = $this->repository->update($gameGenreEntity);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $gameGenreEntity = new GameGenreEntity(
            $id
        );

        try {
            $gameGenreEntity->validateId();

            $wasDeleted = $this->repository->delete($gameGenreEntity);

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): GameGenreEntity|null
    {
        $gameGenreEntity = new GameGenreEntity(
            $id
        );

        try {
            $gameGenreEntity->validateId();

            $fetchedGameGenreEntity = $this->repository->findById(
                $gameGenreEntity->getId()
            );

            return $fetchedGameGenreEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $fetchedGameGenreEntities = $this->repository->findAll();

            return $fetchedGameGenreEntities;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
