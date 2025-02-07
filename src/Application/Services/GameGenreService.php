<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Exception;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;
use PDOException;

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
    public function insert(int $genreId, int $gameId): GameGenre
    {
        $gameGenre = new GameGenre();
        $gameGenre->setGenreId($genreId);
        $gameGenre->setGameId($gameId);

        try {
            $gameGenre->validateGameId();
            $gameGenre->validateGenreId();
            $gameGenre = $this->repository->insert($gameGenre);
            return $gameGenre;
        } catch (EntityInvalidValueException | PDOException | Exception $e) {
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
    public function update(int $id, int $genreId, int $gameId): bool
    {
        $gameGenre = new GameGenre();
        $gameGenre->setGameId($gameId);
        $gameGenre->setGenreId($genreId);
        $gameGenre->setGameId($gameId);

        try {
            $gameGenre->validateId();
            $gameGenre->validateGenreId();
            $gameGenre->validateGameId();
            $wasItSuccessful = $this->repository->update($gameGenre);
            return $wasItSuccessful;
        } catch (EntityInvalidValueException | PDOException | Exception $e) {
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
    public function delete(int $id): bool
    {
        $gameGenre = new GameGenre();
        $gameGenre->setId($id);

        try {
            $gameGenre->validateId();
            $wasItSuccessful = $this->repository->delete($gameGenre);
            return $wasItSuccessful;
        } catch (EntityInvalidValueException | PDOException | Exception $e) {
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
