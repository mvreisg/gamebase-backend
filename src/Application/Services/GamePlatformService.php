<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use PDOException;

class GamePlatformService
{
    private GamePlatformRepositoryInterface $repository;

    public function __construct(GamePlatformRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(mixed $platformId, mixed $gameId): GamePlatform
    {
        $gamePlatform = new GamePlatform();

        try {
            $gamePlatform->validatePlatformId($platformId);
            $gamePlatform->validateGameId($gameId);
            $gamePlatform->setPlatformId($platformId);
            $gamePlatform->setGameId($gameId);
            $gamePlatform = $this->repository->insert($gamePlatform);

            return $gamePlatform;
        } catch (
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function update(mixed $id, mixed $platformId, mixed $gameId): bool
    {
        $gamePlatform = new GamePlatform();

        try {
            $gamePlatform->validateId($id);
            $gamePlatform->validatePlatformId($platformId);
            $gamePlatform->validateGameId($gameId);
            $gamePlatform->setId($id);
            $gamePlatform->setPlatformId($platformId);
            $gamePlatform->setGameId($gameId);
            $wasTheUpdateSuccessful = $this->repository->update($gamePlatform);
            return $wasTheUpdateSuccessful;
        } catch (
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function delete(mixed $id): bool
    {
        $gamePlatform = new GamePlatform();

        try {
            $gamePlatform->validateId($id);
            $gamePlatform->setId($id);
            $wasTheDeletionSuccessful = $this->repository->delete($gamePlatform);
            return $wasTheDeletionSuccessful;
        } catch (EntityInvalidValueException | DatabaseStatementCreationFailureException | PDOException $e) {
            throw $e;
        }
    }

    public function findById(mixed $id): GamePlatform|null
    {
        $gamePlatform = new GamePlatform();
        try {
            $gamePlatform->validateId($id);
            $gamePlatform->setId($id);
            $gamePlatform = $this->repository->findById($id);
            return $gamePlatform;
        } catch (
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $gamePlatforms = $this->repository->findAll();
            return $gamePlatforms;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }
}
