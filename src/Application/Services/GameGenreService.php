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
        $gameGenre->setGenreId($genreId);
        $gameGenre->setGameId($gameId);

        try {
            $gameGenre->validateGameId();
            $gameGenre->validateGenreId();
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
        $gameGenre->setId($id);
        $gameGenre->setGenreId($genreId);
        $gameGenre->setGameId($gameId);

        try {
            $gameGenre->validateId();
            $gameGenre->validateGenreId();
            $gameGenre->validateGameId();
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
        $gameGenre->setId($id);

        try {
            $gameGenre->validateId();
            $wasTheDeleteSuccessful = $this->repository->delete($gameGenre);
            return $wasTheDeleteSuccessful;
        } catch (EntityInvalidValueException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Deletes all Game Genres with the respective Game id.
     * @param int $gameId The Game id.
     * @return bool The success flag.
     * @throws EntityInvalidValueException Throwed if the entity has an invalid value.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed if any error occurs.
     */
    public function deleteAllByGameId(int $gameId): bool
    {
        $gameGenre = new GameGenre();
        $gameGenre->setGameId($gameId);

        try {
            $gameGenre->validateGameId();
            $wasItSuccessful = $this->repository->deleteAllByGameId($gameGenre);
            return $wasItSuccessful;
        } catch (EntityInvalidValueException | PDOException | Exception $e) {
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
    public function findById(int $id): GameGenre|null
    {
        $gameGenre = new GameGenre();
        $gameGenre->setId($id);

        try {
            $gameGenre->validateId();
            $gameGenre = $this->repository->findById($id);
            return $gameGenre;
        } catch (EntityInvalidValueException | PDOException | Exception $e) {
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
            $result = $this->repository->findAll();
            return $result;
        } catch (PDOException | Exception $e) {
            throw $e;
        }
    }

    /**
     * Finds all Game Genres with the respective Game id.
     * @param int $gameId The Game id.
     * @return array A list of the Game Genres.
     * @throws EntityInvalidValueException Throwed if the entity has an invalid value.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed if any error occurs.
     */
    public function findAllGameGenresByGameId(int $gameId): array
    {
        $gameGenre = new GameGenre();
        $gameGenre->setGameId($gameId);

        try {
            $gameGenre->validateGameId();
            $gameGenres = $this->repository->findAllGameGenresByGameId($gameId);
            return $gameGenres;
        } catch (EntityInvalidValueException | PDOException | Exception $e) {
            throw $e;
        }
    }

    /**
     * Returns all Game Genres where the Game intersects with Genre based on the Game id.
     * @return array A list of the Game Genres.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed if any error occurs.
     */
    public function intersectionBetweenGameAndGameGenreByGameId(): array
    {
        try {
            $gameGenres = $this->repository->innerJoinBetweenGameAndGameGenreByGameId();
            return $gameGenres;
        } catch (PDOException | Exception $e) {
            throw $e;
        }
    }
}
