<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use PDOException;
use Throwable;

class GameGenreService
{
    private GameGenreRepositoryInterface $repository;

    public function __construct(GameGenreRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(int $genreId, int $gameId): GameGenre
    {
        $gameGenre = new GameGenre();

        try {
            $gameGenre->setGenreId($genreId);
            $gameGenre->setGameId($gameId);

            $gameGenre->validateGenreId();
            $gameGenre->validateGameId();

            $gameGenre = $this->repository->insert($gameGenre);

            return $gameGenre;
        } catch (
            EntityInvalidValueException |
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException |
            Throwable $e
        ) {
            throw $e;
        }
    }

    public function update(int $id, int $genreId, int $gameId): bool
    {
        $gameGenre = new GameGenre();

        try {
            $gameGenre->setId($id);
            $gameGenre->setGenreId($genreId);
            $gameGenre->setGameId($gameId);

            $gameGenre->validateId();
            $gameGenre->validateGenreId();
            $gameGenre->validateGameId();

            $wasTheUpdateSuccessful = $this->repository->update($gameGenre);

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
        $gameGenre = new GameGenre();

        try {
            $gameGenre->setId($id);

            $gameGenre->validateId();

            $wasTheDeleteSuccessful = $this->repository->delete($gameGenre);

            return $wasTheDeleteSuccessful;
        } catch (
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function findById(int $id): GameGenre|null
    {
        $gameGenre = new GameGenre();

        try {
            $gameGenre->setId($id);

            $gameGenre->validateId();

            $gameGenre = $this->repository->findById($id);

            return $gameGenre;
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
            $gameGenres = $this->repository->findAll();

            return $gameGenres;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }
}
