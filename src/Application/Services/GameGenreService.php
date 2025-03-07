<?php

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

    public function insert(mixed $genreId, mixed $gameId): GameGenre
    {
        $gameGenre = new GameGenre();

        try {
            $gameGenre->validateGenreId($genreId);
            $gameGenre->validateGameId($gameId);
            $gameGenre->setGenreId($genreId);
            $gameGenre->setGameId($gameId);
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

    public function update(mixed $id, mixed $genreId, mixed $gameId): bool
    {
        $gameGenre = new GameGenre();

        try {
            $gameGenre->validateId($id);
            $gameGenre->validateGenreId($genreId);
            $gameGenre->validateGameId($gameId);
            $gameGenre->setId($id);
            $gameGenre->setGenreId($genreId);
            $gameGenre->setGameId($gameId);
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

    public function delete(mixed $id): bool
    {
        $gameGenre = new GameGenre();

        try {
            $gameGenre->validateId($id);
            $gameGenre->setId($id);
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

    public function findById(mixed $id): GameGenre|null
    {
        $gameGenre = new GameGenre();

        try {
            $gameGenre->validateId($id);
            $gameGenre->setId($id);
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
