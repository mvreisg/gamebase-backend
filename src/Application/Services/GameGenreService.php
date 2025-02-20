<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Exception;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use PDOException;
use Throwable;

/**
 * Game Genre service class.
 */
class GameGenreService
{
    /**
     * @var GameGenreRepositoryInterface $repository The repository to be used by this service.
     */
    private GameGenreRepositoryInterface $repository;

    /**
     * Game Genre service class constructor.
     * @param GameGenreRepositoryInterface $repository The repository to be used by this service.
     * @return void
     */
    public function __construct(GameGenreRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Inserts a new Game Genre.
     * @param int $genreId The Genre id.
     * @param int $gameId The Game id.
     * @return GameGenre A copy of the inserted object.
     * @throws EntityInvalidValueException Throwed if the entity has an invalid value.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed if any error occurs.
     */
    public function insert(mixed $genreId, mixed $gameId): GameGenre
    {
        $gameGenre = new GameGenre();

        try {
            $gameGenre->validateGameId($gameId);
            $gameGenre->validateGenreId($gameGenre);
            $gameGenre->setGameId($gameId);
            $gameGenre->setGenreId($genreId);
            $gameGenre = $this->repository->insert($gameGenre);
            return $gameGenre;
        } catch (EntityInvalidValueException | DatabaseTransactionCreationFailureException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | DatabaseFetchFailureException | PDOException | Throwable $e) {
            throw $e;
        }
    }

    /**
     * Updates a existing Game Genre.
     * @param int $id The id of the entity.
     * @param int $genreId The Genre id.
     * @param int $gameId The Game id.
     * @return bool The success flag.
     * @throws EntityInvalidValueException Throwed if the entity has an invalid value.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed if any error occurs.
     */
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
        } catch (EntityInvalidValueException | DatabaseStatementCreationFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Deletes a existing Game Genre.
     * @param int $id The id of the entity.
     * @return bool The success flag.
     * @throws EntityInvalidValueException Throwed if the entity has an invalid value.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed if any error occurs.
     */
    public function delete(mixed $id): bool
    {
        $gameGenre = new GameGenre();

        try {
            $gameGenre->validateId($id);
            $gameGenre->setId($id);
            $wasTheDeleteSuccessful = $this->repository->delete($gameGenre);
            return $wasTheDeleteSuccessful;
        } catch (EntityInvalidValueException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Finds a Game Genre by its id.
     * @param int $id The id to search.
     * @return GameGenre The found Game Genre, else null.
     * @throws EntityInvalidValueException Throwed if the entity has an invalid value.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed if any error occurs.
     */
    public function findById(mixed $id): GameGenre|null
    {
        $gameGenre = new GameGenre();

        try {
            $gameGenre->validateId($id);
            $gameGenre->setId($id);
            $gameGenre = $this->repository->findById($id);
            return $gameGenre;
        } catch (EntityInvalidValueException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Finds all the Game Genre registers.
     * @return array A list containing all the found Game Genres.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed if any error occurs.
     */
    public function findAll(): array
    {
        try {
            $gameGenres = $this->repository->findAll();
            return $gameGenres;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }
}
