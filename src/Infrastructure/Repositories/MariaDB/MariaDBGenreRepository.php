<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use PDO;
use Mvreisg\GamebaseBackend\Domain\Entities\Genre\Genre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBDuplicatedNameException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBUnexistantRegisterException;
use PDOException;

class MariaDBGenreRepository implements GenreRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(Genre $genre): Genre
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBTransactionCreationFailureException();
            }

            $name = $genre->getName();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $genre->getIsActive()
            );

            $insertStatement = $this->pdo->prepare(
                "INSERT INTO 
                    genre (
                        name,
                        is_active
                    ) 
                VALUES 
                    (
                        :name,
                        :isActive
                    );"
            );

            if ($insertStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasInsertStatementExecutedSuccessfully = $insertStatement->execute([
                ":name" => $name,
                ":isActive" => $isActive
            ]);

            if ($wasInsertStatementExecutedSuccessfully === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $lastInsertedId = intval(
                $this->pdo->lastInsertId()
            );

            $selectStatement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    genre 
                WHERE 
                    id = :id;"
            );

            if ($selectStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasSelectStatementSuccessfullyExecuted = $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);

            if ($wasSelectStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $selectStatement->fetch();

            if ($fetchResult === false) {
                throw new MariaDBFetchFailureException();
            }

            $this->pdo->commit();

            return new Genre(
                $fetchResult["id"],
                $fetchResult["name"],
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult["is_active"]
                )
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

    public function update(Genre $genre): bool
    {
        try {
            $id = $genre->getId();
            $name = $genre->getName();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $genre->getIsActive()
            );

            $statement = $this->pdo->prepare(
                "UPDATE 
                    genre 
                SET 
                    name = :name, 
                    is_active = :isActive 
                WHERE 
                    id = :id;"
            );

            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheUpdateSuccessfullyExecuted = $statement->execute([
                ":name" => $name,
                ":id" => $id,
                ":isActive" => $isActive
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

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $intIsActive = intval($isActive);

            $statement = $this->pdo->prepare(
                "UPDATE
                    genre
                SET
                    is_active = :isActive
                WHERE
                    id = :id
                AND
                    is_active <> :isActive;"
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheUpdateSuccessfullyExecuted = $statement->execute([
                ":id" => $id,
                ":isActive" => $intIsActive
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

    public function findById(int $id): Genre
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    genre 
                WHERE 
                    id = :id;"
            );

            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ":id" => $id
            ]);

            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBUnexistantRegisterException(
                    "Unexistant register with the id $id"
                );
            }

            return new Genre(
                $fetchResult["id"],
                $fetchResult["name"],
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult["is_active"]
                )
            );
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
                "SELECT 
                    * 
                FROM 
                    genre;"
            );

            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute();

            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetchAll();

            if ($fetchResult === false) {
                return [];
            }

            $genres = [];

            foreach ($fetchResult as $row) {
                $genres[] = new Genre(
                    $row["id"],
                    $row["name"],
                    /* MariaDB stores bool as int values so a casting
                    * here is needed.
                    */
                    boolval(
                        $row["is_active"]
                    )
                );
            }

            return $genres;
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
                    genre
                WHERE
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheCheckSuccessfullyExecuted = $statement->execute([
                ":id" => $id
            ]);
            if ($wasTheCheckSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            $numberOfIds = intval(
                $fetchResult["number"]
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
                "SELECT 
                    COUNT(*)
                    AS
                    number_of_names
                FROM 
                    genre 
                WHERE 
                    name = :name;"
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementExecutedSuccessfully = $statement->execute([
                ":name" => $name
            ]);
            if ($wasTheStatementExecutedSuccessfully === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            $numberOfNames = intval(
                $fetchResult["number_of_names"]
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
            PDOException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }
}
