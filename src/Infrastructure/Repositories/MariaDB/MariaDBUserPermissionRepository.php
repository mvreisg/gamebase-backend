<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\UserPermission;
use Mvreisg\GamebaseBackend\Domain\Data\UserPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryUnexistantRegisterException;

class MariaDBUserPermissionRepository implements UserPermissionRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(UserPermission $userPermission): UserPermission
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBRepositoryTransactionCreationFailureException();
            }

            $userId = $userPermission->getUserIdValue();
            $permissionId = $userPermission->getPermissionIdValue();

            $insertStatement = $this->pdo->prepare(
                "INSERT INTO user_permission (
                    user_id, 
                    permission_id
                ) 
                VALUES (
                    :userId, 
                    :permissionId
                );"
            );
            if ($insertStatement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheInsertStatementSuccessfullyExecuted = $insertStatement->execute([
                ":userId" => $userId,
                ":permissionId" => $permissionId
            ]);
            if ($wasTheInsertStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $lastInsertedId = intval(
                $this->pdo->lastInsertId()
            );

            $selectStatement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    user_permission 
                WHERE 
                    id = :id;"
            );
            if ($selectStatement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheSelectStatementSuccessfullyExecuted = $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);
            if ($wasTheSelectStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $selectStatement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBRepositoryStatementFetchFailureException();
            }

            $this->pdo->commit();

            $return = new UserPermission(
                Id::make($fetchResult["user_id"]),
                Id::make($fetchResult["permission_id"])
            );
            $return->setId(Id::make($fetchResult["id"]));
            return $return;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(UserPermission $userPermission): bool
    {
        try {
            $id = $userPermission->getIdValue();
            $userId = $userPermission->getUserIdValue();
            $permissionId = $userPermission->getPermissionIdValue();

            $statement = $this->pdo->prepare(
                "UPDATE 
                    user_permission 
                SET 
                    user_id = :userId, 
                    permission_id = :permissionId 
                WHERE 
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementExecutionSuccessful = $statement->execute([
                ":id" => $id,
                ":userId" => $userId,
                ":permissionId" => $permissionId
            ]);
            if ($wasTheStatementExecutionSuccessful === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(Id $id): bool
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->pdo->prepare(
                "DELETE FROM
                    user_permission
                WHERE
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheDeleteStatementSuccessfullyExecuted = $statement->execute([
                ":id" => $idValue
            ]);
            if ($wasTheDeleteStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): UserPermission
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    user_permission 
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

            $return = new UserPermission(
                Id::make($fetchResult["user_id"]),
                Id::make($fetchResult["permission_id"])
            );
            $return->setId(Id::make($fetchResult["id"]));
            return $return;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAllByUserId(Id $userId): UserPermissionCollection
    {
        try {
            $userIdValue = $userId->getValue();

            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    user_permission
                WHERE
                    user_id = :userId;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ":userId" => $userIdValue
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetchAll();
            if ($fetchResult === false) {
                return new UserPermissionCollection();
            }

            $userPermissions = new UserPermissionCollection();
            foreach ($fetchResult as $row) {
                $value = new UserPermission(
                    Id::make($row["user_id"]),
                    Id::make($row["permission_id"])
                );
                $value->setId(Id::make($row["id"]));
                $userPermissions->add($value);
            }
            return $userPermissions;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): UserPermissionCollection
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    user_permission;"
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
                return new UserPermissionCollection();
            }

            $userPermissions = new UserPermissionCollection();
            foreach ($fetchResult as $row) {
                $value = new UserPermission(
                    Id::make($row["user_id"]),
                    Id::make($row["permission_id"])
                );
                $value->setId(Id::make($row["id"]));
                $userPermissions->add($value);
            }
            return $userPermissions;
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
                    user_permission
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
}
