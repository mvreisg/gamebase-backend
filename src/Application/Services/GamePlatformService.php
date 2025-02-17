<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Exception;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use PDOException;

/**
 * Game Platform service class.
 */
class GamePlatformService
{
    /**
     * @var GamePlatformRepositoryInterface $repository The repository to be user by this service.
     */
    private GamePlatformRepositoryInterface $repository;

    /**
     * Game Platform service class.
     * @param GamePlatformRepositoryInterface $repository The repository to be user by this service.
     * @return void
     */
    public function __construct(GamePlatformRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Inserts a new Game Platform.
     * @param int $platformId The Platform id.
     * @param int $gameId The Game id.
     * @return GamePlatform A copy of the inserted object.
     * @throws EntityInvalidValueException Throwed if any of the informed ids are invalid.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed in case of another error.
     */
    public function insert(mixed $platformId, mixed $gameId): GamePlatform
    {
        $gamePlatform = new GamePlatform();
        $gamePlatform->setPlatformId($platformId);
        $gamePlatform->setGameId($gameId);

        try {
            $gamePlatform->validatePlatformId();
            $gamePlatform->validateGameId();
            $gamePlatform = $this->repository->insert($gamePlatform);

            return $gamePlatform;
        } catch (DatabaseTransactionCreationFailureException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | DatabaseFetchFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Updates a Game Platform.
     * @param int $id The Game Platform id.
     * @param int $platfomrId The Platform id.
     * @param int $gameId The Game id.
     * @return bool The success flag.
     * @throws EntityInvalidValueException Throwed if any of the informed ids are invalid.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed in case of another error.
     */
    public function update(mixed $id, mixed $platformId, mixed $gameId): bool
    {
        $gamePlatform = new GamePlatform();
        $gamePlatform->setId($id);
        $gamePlatform->setPlatformId($platformId);
        $gamePlatform->setGameId($gameId);

        try {
            $gamePlatform->validateId();
            $gamePlatform->validatePlatformId();
            $gamePlatform->validateGameId();
            $wasTheUpdateSuccessful = $this->repository->update($gamePlatform);
            return $wasTheUpdateSuccessful;
        } catch (EntityInvalidValueException | DatabaseStatementCreationFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Deletes a Game Platform.
     * @param int $id The id to the Game Platform to be deleted.
     * @return bool The success flag.
     * @throws EntityInvalidValueException Throwed if any of the informed ids are invalid.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed in case of another error.
     */
    public function delete(mixed $id): bool
    {
        $gamePlatform = new GamePlatform();
        $gamePlatform->setId($id);

        try {
            $gamePlatform->validateId();
            $wasTheDeletionSuccessful = $this->repository->delete($gamePlatform);
            return $wasTheDeletionSuccessful;
        } catch (EntityInvalidValueException | DatabaseStatementCreationFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Deletes all Game Platforms by the informed Game id.
     * @param int $gameId The Game id.
     * @return bool The success flag.
     * @throws EntityInvalidValueException Throwed if any of the informed ids are invalid.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed in case of another error.
     */
    public function deleteAllByGameId(int $gameId): bool
    {
        $gamePlatform = new GamePlatform();
        $gamePlatform->setGameId($gameId);

        try {
            $gamePlatform->validateGameId();
            $wasItSuccessful = $this->repository->deleteAllByGameId($gamePlatform);
            return $wasItSuccessful;
        } catch (EntityInvalidValueException | PDOException | Exception $e) {
            throw $e;
        }
    }

    /**
     * Finds a Game Platform by the informed id.
     * @param int $id The id to search.
     * @return GamePlatform|null The Game Platform if found, else null.
     * @throws EntityInvalidValueException Throwed if any of the informed ids are invalid.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed in case of another error.
     */
    public function findById(int $id): GamePlatform|null
    {
        $gamePlatform = new GamePlatform();
        $gamePlatform->setId($id);
        try {
            $gamePlatform->validateId();
            $gamePlatform = $this->repository->findById($id);
            return $gamePlatform;
        } catch (EntityInvalidValueException | PDOException | Exception $e) {
            throw $e;
        }
    }

    /**
     * Finds all Game Platforms
     * @return array A list containing all the Game Platforms.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed in case of another error.
     */
    public function findAll(): array
    {
        try {
            $gamePlatforms = $this->repository->findAll();
            return $gamePlatforms;
        } catch (PDOException | Exception $e) {
            throw $e;
        }
    }

    /**
     * Finds all Game Platforms by the informed Game id.
     * @param int $gameId The Game id.
     * @return array A list containing the Game Plaforms.
     * @throws EntityInvalidValueException Throwed if any of the informed ids are invalid.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed in case of another error.
     */
    public function findAllGamePlatformsByGameId(int $gameId): array
    {
        $gamePlatform = new GamePlatform();
        $gamePlatform->setGameId($gameId);
        try {
            $gamePlatform->validateId();
            $gamePlatforms = $this->repository->findAllGamePlatformsByGameId($gameId);
            return $gamePlatforms;
        } catch (EntityInvalidValueException | PDOException | Exception $e) {
            throw $e;
        }
    }

    /**
     * Finds all Game and Game Platform data intersected by Game id.
     * @return array A list containing the data.
     * @throws PDOException Throwed if a database connection error occurs.
     * @throws Exception Throwed in case of another error.
     */
    public function intersectionBetweenGameAndGamePlatformByGameId(): array
    {
        try {
            $gamePlatforms = $this->repository->innerJoinBetweenGameAndGamePlatformByGameId();
            return $gamePlatforms;
        } catch (PDOException | Exception $e) {
            throw $e;
        }
    }
}
