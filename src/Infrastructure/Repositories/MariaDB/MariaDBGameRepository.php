<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use PDO;
use Mvreisg\GamebaseBackend\Domain\Entities\Game\Game;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBDuplicatedNameException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBUnexistantRegisterException;
use PDOException;

class MariaDBGameRepository implements GameRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(Game $game): Game
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBTransactionCreationFailureException();
            }

            $name = $game->getName();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $game->getIsActive()
            );

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
                throw new MariaDBStatementCreationFailureException();
            }

            $wasInsertExecutionASuccess = $insertStatement->execute([
                ':name' => $name,
                ':isActive' => $isActive
            ]);

            if ($wasInsertExecutionASuccess === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $lastInsertedId = intval(
                $this->pdo->lastInsertId()
            );

            $selectStatement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    game 
                WHERE 
                    id = :id;'
            );

            if ($selectStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasSelectExecutionASuccess = $selectStatement->execute([
                ':id' => $lastInsertedId
            ]);

            if ($wasSelectExecutionASuccess === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $selectStatement->fetch();

            if ($fetchResult === false) {
                throw new MariaDBFetchFailureException();
            }

            $this->pdo->commit();

            return new Game(
                $fetchResult['id'],
                $fetchResult['name'],
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult['is_active']
                ),
            );
        } catch (
            MariaDBTransactionCreationFailureException |
            MariaDBStatementCreationFailureException |
            MariaDBStatementExecutionFailureException |
            MariaDBFetchFailureException |
            PDOException |
            \Throwable
            $e
        ) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(Game $game): bool
    {
        try {
            $id = $game->getId();
            $name = $game->getName();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $game->getIsActive()
            );

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
                throw new MariaDBStatementCreationFailureException();
            }

            $wasStatementExecutionSuccessful = $statement->execute([
                ':name' => $name,
                ':id' => $id,
                ':isActive' => $isActive
            ]);

            if ($wasStatementExecutionSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (
            MariaDBStatementCreationFailureException |
            MariaDBStatementExecutionFailureException |
            PDOException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $intIsActive = intval($isActive);

            $statement = $this->pdo->prepare(
                'UPDATE
                    game
                SET
                    is_active = :isActive
                WHERE
                    id = :id
                AND
                    is_active <> :isActive;'
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheUpdateSuccessfullyExecuted = $statement->execute([
                ':isActive' => $intIsActive,
                ':id' => $id
            ]);
            if ($wasTheUpdateSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (
            MariaDBStatementCreationFailureException |
            MariaDBStatementExecutionFailureException |
            PDOException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function findById(int $id): Game
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
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':id' => $id
            ]);

            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();

            if ($fetchResult === false) {
                throw new MariaDBUnexistantRegisterException(
                    "Unexistant register with the id $id."
                );
            }

            $game = new Game(
                $fetchResult['id'],
                $fetchResult['name'],
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult['is_active']
                )
            );

            return $game;
        } catch (
            MariaDBStatementCreationFailureException |
            MariaDBStatementExecutionFailureException |
            MariaDBUnexistantRegisterException |
            PDOException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

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
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementExecutionSuccessful = $statement->execute();

            if ($wasTheStatementExecutionSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetchAll();

            if ($fetchResult === false) {
                return [];
            }

            $games = [];
            foreach ($fetchResult as $row) {
                $game = new Game(
                    $row['id'],
                    $row['name'],
                    /* MariaDB stores bool as int values so a casting
                     * here is needed.
                     */
                    boolval(
                        $row['is_active']
                    )
                );
                $games[] = $game;
            }

            return $games;
        } catch (
            MariaDBStatementCreationFailureException |
            MariaDBStatementExecutionFailureException |
            PDOException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function checkIfExists(int $id): void
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT
                    COUNT(*) 
                    AS
                    number
                FROM
                    game
                WHERE
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheCheckSuccessfullyExecuted = $statement->execute([
                ':id' => $id
            ]);
            if ($wasTheCheckSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            $numberOfIds = intval(
                $fetchResult['number']
            );

            if ($numberOfIds === 0) {
                throw new MariaDBUnexistantRegisterException(
                    "Unexistant register with the id $id."
                );
            }
        } catch (
            MariaDBStatementCreationFailureException |
            MariaDBStatementExecutionFailureException |
            PDOException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function checkDuplicatedNames(string $name): void
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    COUNT(*)
                    AS
                    number_of_names
                FROM 
                    game 
                WHERE 
                    name = :name;'
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':name' => $name
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            $numberOfNames = intval(
                $fetchResult['number_of_names']
            );
            if ($numberOfNames > 0) {
                throw new MariaDBDuplicatedNameException(
                    "Duplicated name: $name"
                );
            }
        } catch (
            MariaDBStatementCreationFailureException |
            MariaDBStatementExecutionFailureException |
            MariaDBDuplicatedNameException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }
}
