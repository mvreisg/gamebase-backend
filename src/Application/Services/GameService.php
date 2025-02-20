<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Exception;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\Game;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;

/**
 * The Game service class.
 */
class GameService
{
    /**
     * @var GameRepositoryInterface $repository The repository to be used by the service.
     */
    private GameRepositoryInterface $repository;

    /**
     * The Game service class constructor.
     * @param GameRepositoryInterface $repository The repository to bu used by the service.
     */
    public function __construct(GameRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Inserts a new Game object based on the passed data.
     * @param string $name The Game name.
     * @return Game The Game object created.
     * @throws DatabaseStatementCreationFailureException Throwed in case PDO tries to create a statement then fails.
     * @throws DatabaseStatementExecutionFailureException Throwed in case of a PDO execute fails.
     * @throws DatabaseFetchErrorException Throwed if the PDO fails to fetch data from the database.
     * @throws DatabaseDuplicatedEntryException Throwed in case of database error.
     * @throws PDOException Throwed if a PDO database action error occurs.
     * @throws EntityInvalidValueException Throwed in case of a value in the entity is invalid.
     */
    public function insert(mixed $name, mixed $isActive): Game
    {
        $game = new Game();

        try {
            $game->validateName($name);
            $game->validateIsActive($isActive);
            $game->setName($name);
            $game->setIsActive($isActive);
            $validatedName = $game->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException('O nome do jogo a ser inserido já existe no banco de dados!');
            }
            $game = $this->repository->insert($game);
            return $game;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | DatabaseFetchFailureException | DatabaseDuplicatedEntryException | PDOException | EntityInvalidValueException $e) {
            throw $e;
        }
    }

    /**
     * Updated a registered Game based on the values passed.
     * @param int $id The id of the Game that is wanted to be updated.
     * @param string $name The name of the Game that is wanted to be updated.
     * @return bool Returns the success flag.
     * @throws DatabaseDuplicatedEntryException Throwed in case of database error.
     * @throws EntityInvalidValueException Throwed in case of entity error.
     * @throws PDOException Throwed in case of database connection error.
     * @throws Exception Throwed in case of error.
     */
    public function update(mixed $id, mixed $name, mixed $isActive): bool
    {
        $game = new Game();

        try {
            $game->validateId($id);
            $game->validateName($name);
            $game->validateIsActive($isActive);
            $game->setId($id);
            $game->setName($name);
            $game->setIsActive($isActive);
            $validatedName = $game->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException('O nome do jogo a ser atualizado já existe no repositório!');
            }
            $wasSomeUpdateHappened = $this->repository->update($game);
            return $wasSomeUpdateHappened;
        } catch (EntityInvalidValueException | DatabaseDuplicatedEntryException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }

    public function setIsActive(mixed $id, mixed $isActive): bool
    {
        $game = new Game();

        try {
            $game->validateId($id);
            $game->validateIsActive($isActive);
            $game->setId($id);
            $game->setIsActive($isActive);
            $wasTheUpdateSuccessful = $this->repository->setIsActive($id, $isActive);
            return $wasTheUpdateSuccessful;
        } catch (EntityInvalidValueException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Finds a Game already registered based on the passed id.
     * @param int $id The Game id.
     * @return Game|null Returns Game if the Game with the respective id was found, else returns null.
     * @throws EntityInvalidValueException Throwed in case of entity error.
     * @throws PDOException Throwed in case of database connection error.
     * @throws Exception Throwed in case of error.
     */
    public function findById(mixed $id): Game|null
    {
        $game = new Game();

        try {
            $game->validateId($id);
            $game->setId($id);
            $game = $this->repository->findById($id);
            return $game;
        } catch (EntityInvalidValueException | DatabaseFetchFailureException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Finds and returns all Game registers.
     * @return array A list containing all the found registers in the repository.
     * @throws PDOException Throwed in case of database connection error.
     * @throws Exception Throwed in case of error.
     */
    public function findAll(): array
    {
        try {
            $games = $this->repository->findAll();
            return $games;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }
}
