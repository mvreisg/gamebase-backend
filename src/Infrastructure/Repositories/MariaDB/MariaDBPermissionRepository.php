<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Data\Permission;
use Mvreisg\GamebaseBackend\Domain\Data\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryUnexistantRegisterException;

class MariaDBPermissionRepository implements PermissionRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(Permission $permission): Permission
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBRepositoryTransactionCreationFailureException();
            }

            $name = $permission->getNameValue();

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
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheInsertSuccessful = $insertStatement->execute([
                ":name" => $name,
                ":isActive" => $isActive
            ]);
            if ($wasTheInsertSuccessful === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
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
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheSelectSuccessful = $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);
            if ($wasTheSelectSuccessful === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $selectStatement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBRepositoryStatementFetchFailureException();
            }

            $this->pdo->commit();

            return new Permission(
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

    public function update(Permission $permission): bool
    {
        try {
            $id = $permission->getIdValue();
            $name = $permission->getNameValue();

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
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheUpdateSuccessful = $statement->execute([
                ":name" => $name,
                ":isActive" => $isActive,
                ":id" => $id
            ]);
            if ($wasTheUpdateSuccessful === false) {
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
                    permission
                SET
                    is_active = :isActive
                WHERE
                    id = :id;"
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

    public function findById(Id $id): Permission
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM
                    permission
                WHERE
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheFetchSuccessful = $statement->execute([
                ":id" => $idValue
            ]);
            if ($wasTheFetchSuccessful === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBRepositoryUnexistantRegisterException(
                    $idValue
                );
            }

            return new Permission(
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

    public function findAll(): PermissionCollection
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT 
                    *
                FROM
                    permission;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheSelectSuccessful = $statement->execute();
            if ($wasTheSelectSuccessful === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetchAll();
            if ($fetchResult === false) {
                return new PermissionCollection(null);
            }

            $permissions = new PermissionCollection(null);
            foreach ($fetchResult as $row) {
                $permissions->add(
                    new Permission(
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

            return $permissions;
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
                    permission
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
                    permission 
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
