<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Domain\Entities\Permission\Permission;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBDuplicatedNameException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBUnexistantRegisterException;
use PDO;
use PDOException;

class MariaDBPermissionRepository implements PermissionRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(Permission $permission): Permission
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBTransactionCreationFailureException();
            }

            $name = $permission->getName();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $permission->getIsActive()
            );

            $insertStatement = $this->pdo->prepare(
                "INSERT INTO 
                    permission 
                (
                    name,
                    is_active
                )
                VALUES (
                    :name,
                    :isActive
                );"
            );
            if ($insertStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheInsertSuccessful = $insertStatement->execute([
                ":name" => $name,
                ":isActive" => $isActive
            ]);
            if ($wasTheInsertSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $lastInsertedId = intval(
                $this->pdo->lastInsertId()
            );

            $selectStatement = $this->pdo->prepare(
                "SELECT 
                    *
                FROM
                    permission
                WHERE
                    id = :id;"
            );
            if ($selectStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheSelectSuccessful = $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);
            if ($wasTheSelectSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $selectStatement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBFetchFailureException();
            }

            $this->pdo->commit();

            return new Permission(
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

    public function update(Permission $permission): bool
    {
        try {
            $id = $permission->getId();
            $name = $permission->getName();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $permission->getIsActive()
            );

            $statement = $this->pdo->prepare(
                "UPDATE
                    permission
                SET
                    name = :name,
                    is_active = :isActive
                WHERE
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheUpdateSuccessful = $statement->execute([
                ":name" => $name,
                ":isActive" => $isActive,
                ":id" => $id
            ]);
            if ($wasTheUpdateSuccessful === false) {
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
                    permission
                SET
                    is_active = :isActive
                WHERE
                    id = :id;"
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

    public function findById(int $id): Permission
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM
                    permission
                WHERE
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheFetchSuccessful = $statement->execute([
                ":id" => $id
            ]);
            if ($wasTheFetchSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBUnexistantRegisterException(
                    "Unexistant register with the id $id"
                );
            }

            return new Permission(
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
                    permission;"
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheSelectSuccessful = $statement->execute();
            if ($wasTheSelectSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetchAll();
            if ($fetchResult === false) {
                return [];
            }

            $permissions = [];
            foreach ($fetchResult as $row) {
                $permissions[] = new Permission(
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

            return $permissions;
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
                    permission
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
                    permission 
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
