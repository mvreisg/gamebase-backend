<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\Game;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;

/**
 * The MariaDB Game repository class.
 */
class MariaDBGameRepository implements GameRepositoryInterface
{
    /**
     * @var PDO $pdo The PDO object to make database actions.
     */
    private PDO $pdo;

    /**
     * The MariaDB Game repository class constructor.
     * @param PDO $pdo The PDO object to make dabatase actions.
     * @return void
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Inserts a Game into the repository.
     * @param Game $game The Game object containing the data to be inserted into the repository.
     * @return Game The inserted Game object clone.
     * @throws DatabaseStatementCreationFailureException Throwed in case PDO tries to create a statement then fails.
     * @throws DatabaseStatementExecutionFailureException Throwed in case of a PDO execute fails.
     * @throws DatabaseFetchErrorException Throwed if the PDO fails to fetch data from the database.
     * @throws PDOException Throwed if a PDO database action error occurs.
     */
    public function insert(Game $game): Game
    {
        try {
            $this->pdo->beginTransaction();

            $name = $game->getName();
            $isActive = $game->getIsActive();

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    game (
                        name, 
                        is_active
                    ) 
                VALUES (
                    :name, 
                    :isActive
                );'
            );

            if ($insertStatement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de inserção!');
            }

            $wasInsertExecutionASuccess = $insertStatement->execute([
                ':name' => $name,
                ':isActive' => $isActive
            ]);

            if ($wasInsertExecutionASuccess === false) {
                throw new DatabaseStatementExecutionFailureException('Ocorreu um erro ao executar a declaração de inserção!');
            }

            $lastInsertedId = $this->pdo->lastInsertId();
            $lastInsertedId = intval($lastInsertedId);

            $selectStatement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    game 
                WHERE 
                    id = :id;'
            );

            if ($selectStatement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasSelectExecutionASuccess = $selectStatement->execute([
                ':id' => $lastInsertedId
            ]);

            if ($wasSelectExecutionASuccess === false) {
                throw new DatabaseStatementExecutionFailureException('Ocorreu um erro ao executar a declaração de busca!');
            }

            $fetchResult = $selectStatement->fetch();

            if ($fetchResult === false) {
                throw new DatabaseFetchFailureException('Ocorreu uma falha ao realizar a busca!');
            }

            $this->pdo->commit();

            $game = new Game();
            $game->setId($fetchResult['id']);
            $game->setName($fetchResult['name']);
            $game->setIsActive($fetchResult['is_active']);

            return $game;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | DatabaseFetchFailureException | PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Updates a Game register in the Game repository.
     * @param Game $game The Game object containing the data to be updated into the repository.
     * @return bool Returns the success flag.
     * @throws PDOException Throwed if a PDO database action error occurs.
     */
    public function update(Game $game): bool
    {
        $id = $game->getId();
        $name = $game->getName();
        $isActive = $game->getIsActive();

        try {
            $statement = $this->pdo->prepare(
                'UPDATE 
                    game 
                SET 
                    name = :name, 
                    is_active = :isActive 
                WHERE 
                    id = :id;'
            );

            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasStatementExecutionSuccessful = $statement->execute([
                ':name' => $name,
                ':id' => $id,
                ':isActive' => $isActive
            ]);

            if ($wasStatementExecutionSuccessful === false) {
                throw new DatabaseStatementExecutionFailureException('Ocorreu um erro ao executar a declaração de busca!');
            }

            $numberOfLinesAffected = $statement->rowCount();
            $wasSomeUpdateHappened = $numberOfLinesAffected > 0;
            return $wasSomeUpdateHappened;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Deletes a Game registed in the Game repository by the id.
     * @param int $id The respective id of the Game register that is wanted to be deleted.
     * @return bool Returns the success flag.
     * @throws PDOException Throwed if a PDO database action error occurs.
     */
    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $statement = $this->pdo->prepare(
                'UPDATE
                    game
                SET
                    is_active = :isActive
                WHERE
                    id = :id;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de atualização!');
            }

            $wasTheUpdateSuccessfullyExecuted = $statement->execute([
                ':isActive' => $isActive,
                ':id' => $id
            ]);
            if ($wasTheUpdateSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException('Ocorreu um erro ao executar a declaração de atualização!');
            }

            return $wasTheUpdateSuccessfullyExecuted;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Finds a Game register in the Game repository by its respective id and returns their Game object if it was found.
     * @param int $id The id of the Game register that is wanted to be found.
     * @return Game|null Returns the Game object if id is founded, else returns null.
     * @throws PDOException Throwed if a PDO database action error occurs.
     */
    public function findById(mixed $id): Game|null
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    game 
                WHERE 
                    id = :id;'
            );

            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':id' => $id
            ]);

            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException('Ocorreu um erro ao executar a declaração de busca!');
            }

            $result = $statement->fetch();

            if ($result === false) {
                return null;
            }

            $game = new Game();
            $game->setId($result['id']);
            $game->setName($result['name']);
            $game->setIsActive($result['is_active']);

            return $game;
        } catch (DatabaseFetchFailureException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Finds all the Game registers in the repository.
     * @return array Returns all Games registers found in the Game repository in a list.
     * @throws PDOException Throwed if a PDO database action error occurs.
     */
    public function findAll(): array
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    game;'
            );

            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasTheStatementExecutionSuccessful = $statement->execute();

            if ($wasTheStatementExecutionSuccessful === false) {
                throw new DatabaseStatementExecutionFailureException('Ocorreu um erro ao executar a declaração de busca!');
            }

            $result = $statement->fetchAll();

            if ($result === false) {
                return [];
            }

            $games = [];
            foreach ($result as $row) {
                $game = new Game();
                $game->setId($row['id']);
                $game->setName($row['name']);
                $game->setIsActive($row['is_active']);
                $games[] = $game;
            }

            return $games;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Checks if a register with the name already exists in the repository.
     * @param string $name The Game name.
     * @return bool Returns true if the register already exists, else false.
     * @throws PDOException Throwed if a PDO database action error occurs.
     */
    public function hasDuplicatedNames(string $name): bool
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    game 
                WHERE 
                    name = :name;'
            );

            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao tentar criar a declaração de busca!');
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':name' => $name
            ]);

            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException('Ocorreu um erro ao tentar executar a declaração de busca!');
            }

            $numberOfLinesAffected = $statement->rowCount();
            $hasDuplicatedNames = $numberOfLinesAffected > 0;

            return $hasDuplicatedNames;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }
}
