<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Domain\Data\Genre;
use Mvreisg\GamebaseBackend\Domain\Data\GenreCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryUnexistantRegisterException;

class MariaDBGenreRepository implements GenreRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(Genre $genre): Genre
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBRepositoryTransactionCreationFailureException();
            }

            $name = $genre->getNameValue();

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
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasInsertStatementExecutedSuccessfully = $insertStatement->execute([
                ":name" => $name,
                ":isActive" => $isActive
            ]);
            if ($wasInsertStatementExecutedSuccessfully === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
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
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasSelectStatementSuccessfullyExecuted = $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);
            if ($wasSelectStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $selectStatement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBRepositoryStatementFetchFailureException();
            }

            $this->pdo->commit();

            return new Genre(
                Id::make($fetchResult["id"]),
                new Name($fetchResult["name"]),
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult["is_active"]
                )
            );
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(Genre $genre): bool
    {
        try {
            $id = $genre->getIdValue();
            $name = $genre->getNameValue();

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
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheUpdateSuccessfullyExecuted = $statement->execute([
                ":name" => $name,
                ":id" => $id,
                ":isActive" => $isActive
            ]);
            if ($wasTheUpdateSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        try {
            $idValue = $id->getValue();

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
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheUpdateSuccessfullyExecuted = $statement->execute([
                ":id" => $idValue,
                ":isActive" => $intIsActive
            ]);
            if ($wasTheUpdateSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): Genre
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    genre 
                WHERE 
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ":id" => $idValue
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBRepositoryUnexistantRegisterException(
                    $idValue
                );
            }

            return new Genre(
                Id::make($fetchResult["id"]),
                new Name($fetchResult["name"]),
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult["is_active"]
                )
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): GenreCollection
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    genre;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute();
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetchAll();
            if ($fetchResult === false) {
                return new GenreCollection();
            }

            $genres = new GenreCollection();
            foreach ($fetchResult as $row) {
                $genres->add(
                    new Genre(
                        Id::make($row["id"]),
                        new Name($row["name"]),
                        /* MariaDB stores bool as int values so a casting
                        * here is needed.
                        */
                        boolval(
                            $row["is_active"]
                        )
                    )
                );
            }
            return $genres;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkIfExists(Id $id): void
    {
        try {
            $idValue = $id->getValue();

            $alias = "number_of_ids";
            $statement = $this->pdo->prepare(
                "SELECT
                    COUNT(*) 
                    AS
                    $alias
                FROM
                    genre
                WHERE
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheCheckSuccessfullyExecuted = $statement->execute([
                ":id" => $idValue
            ]);
            if ($wasTheCheckSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            $numberOfIds = intval(
                $fetchResult[
                    $alias
                ]
            );
            if ($numberOfIds === 0) {
                throw new MariaDBRepositoryUnexistantRegisterException(
                    $idValue
                );
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkDuplicatedNames(Name $name): void
    {
        try {
            $nameValue = $name->getValue();

            $alias = "number_of_names";
            $statement = $this->pdo->prepare(
                "SELECT 
                    COUNT(*)
                    AS
                    $alias
                FROM 
                    genre 
                WHERE 
                    name = :name;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementExecutedSuccessfully = $statement->execute([
                ":name" => $nameValue
            ]);
            if ($wasTheStatementExecutedSuccessfully === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            $numberOfNames = intval(
                $fetchResult[
                    $alias
                ]
            );
            if ($numberOfNames > 0) {
                throw new MariaDBRepositoryDuplicatedRegisterException(
                    $nameValue
                );
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
