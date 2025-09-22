<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatformEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformEntityRepositoryInterface;

class GamePlatformService
{
    private GamePlatformEntityRepositoryInterface $repository;

    public function __construct(GamePlatformEntityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(int $platformId, int $gameId): GamePlatformEntity
    {
        $gamePlatformEntity = new GamePlatformEntity(
            PHP_INT_MAX,
            $platformId,
            $gameId
        );

        try {
            $gamePlatformEntity->validatePlatformId();
            $gamePlatformEntity->validateGameId();

            $insertedGamePlatformEntity = $this->repository->insert($gamePlatformEntity);

            return $insertedGamePlatformEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(int $id, int $platformId, int $gameId): bool
    {
        $gamePlatformEntity = new GamePlatformEntity(
            $id,
            $platformId,
            $gameId
        );

        try {
            $gamePlatformEntity->validateId();
            $gamePlatformEntity->validatePlatformId();
            $gamePlatformEntity->validateGameId();

            $wasUpdated = $this->repository->update($gamePlatformEntity);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $gamePlatformEntity = new GamePlatformEntity(
            $id
        );

        try {
            $gamePlatformEntity->validateId();

            $wasDeleted = $this->repository->delete($gamePlatformEntity);

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): GamePlatformEntity|null
    {
        $gamePlatformEntity = new GamePlatformEntity(
            $id
        );

        try {
            $gamePlatformEntity->validateId();

            $validatedId = $gamePlatformEntity->getId();

            $fetchedGamePlatformEntity = $this->repository->findById($validatedId);

            return $fetchedGamePlatformEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $fetchedGamePlatformEntities = $this->repository->findAll();

            return $fetchedGamePlatformEntities;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
