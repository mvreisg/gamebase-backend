<?php

declare(strict_types=1);

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

    public function insert(int $platformId, int $gameId): GamePlatform
    {
        $gamePlatform = new GamePlatform();

        try {
            $gamePlatform->setPlatformId($platformId);
            $gamePlatform->setGameId($gameId);

            $gamePlatform->validatePlatformId();
            $gamePlatform->validateGameId();

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

    public function update(int $id, int $platformId, int $gameId): bool
    {
        $gamePlatform = new GamePlatform();

        try {
            $gamePlatform->setId($id);
            $gamePlatform->setPlatformId($platformId);
            $gamePlatform->setGameId($gameId);

            $gamePlatform->validateId();
            $gamePlatform->validatePlatformId();
            $gamePlatform->validateGameId();

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

    public function delete(int $id): bool
    {
        $gamePlatform = new GamePlatform();

        try {
            $gamePlatform->setId($id);

            $gamePlatform->validateId();

            $wasTheDeletionSuccessful = $this->repository->delete($gamePlatform);

            return $wasTheDeletionSuccessful;
        } catch (EntityInvalidValueException | DatabaseStatementCreationFailureException | PDOException $e) {
            throw $e;
        }
    }

    public function findById(int $id): GamePlatform|null
    {
        $gamePlatform = new GamePlatform();
        try {
            $gamePlatform->setId($id);

            $gamePlatform->validateId();

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
