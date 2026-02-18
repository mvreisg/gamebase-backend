<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\Data\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryUnexistantRegisterException;

class MariaDBUserSectorPermissionRepository implements UserSectorPermissionRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(UserSectorPermission $userSectorPermission): UserSectorPermission
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBRepositoryTransactionCreationFailureException();
            }

            $userId = $userSectorPermission->getUserIdValue();
            $sectorId = $userSectorPermission->getSectorIdValue();
            $permissionId = $userSectorPermission->getPermissionIdValue();

            $insertStatement = $this->pdo->prepare(
                "INSERT INTO user_sector_permission (
                    user_id, 
                    sector_id,
                    permission_id
                ) 
                VALUES (
                    :userId, 
                    :sectorId,
                    :permissionId
                );"
            );
            if ($insertStatement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheInsertStatementSuccessfullyExecuted = $insertStatement->execute([
                ":userId" => $userId,
                ":sectorId" => $sectorId,
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
                    user_sector_permission 
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

            $return = new UserSectorPermission(
                Id::make($fetchResult["user_id"]),
                Id::make($fetchResult["sector_id"]),
                Id::make($fetchResult["permission_id"])
            );
            $return->setId(Id::make($fetchResult["id"]));
            return $return;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(UserSectorPermission $userSectorPermission): bool
    {
        try {
            $id = $userSectorPermission->getIdValue();
            $userId = $userSectorPermission->getUserIdValue();
            $sectorId = $userSectorPermission->getSectorIdValue();
            $permissionId = $userSectorPermission->getPermissionIdValue();

            $statement = $this->pdo->prepare(
                "UPDATE 
                    user_sector_permission 
                SET 
                    user_id = :userId, 
                    sector_id = :sectorId,
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
                ":sectorId" => $sectorId,
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
                    user_sector_permission
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

    public function findById(Id $id): UserSectorPermission
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    user_sector_permission 
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

            $return = new UserSectorPermission(
                Id::make($fetchResult["user_id"]),
                Id::make($fetchResult["sector_id"]),
                Id::make($fetchResult["permission_id"])
            );
            $return->setId(Id::make($fetchResult["id"]));
            return $return;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAllByUserId(Id $userId): UserSectorPermissionCollection
    {
        try {
            $userIdValue = $userId->getValue();

            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    user_sector_permission
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
                return new UserSectorPermissionCollection(null);
            }

            $userSectorPermissions = new UserSectorPermissionCollection(null);
            foreach ($fetchResult as $row) {
                $value = new UserSectorPermission(
                    Id::make($row["user_id"]),
                    Id::make($row["sector_id"]),
                    Id::make($row["permission_id"])
                );
                $value->setId(Id::make($row["id"]));
                $userSectorPermissions->add($value);
            }
            return $userSectorPermissions;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): UserSectorPermissionCollection
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    user_sector_permission;"
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
                return new UserSectorPermissionCollection(null);
            }

            $userSectorPermissions = new UserSectorPermissionCollection(null);
            foreach ($fetchResult as $row) {
                $value = new UserSectorPermission(
                    Id::make($row["user_id"]),
                    Id::make($row["sector_id"]),
                    Id::make($row["permission_id"])
                );
                $value->setId(Id::make($row["id"]));
                $userSectorPermissions->add($value);
            }
            return $userSectorPermissions;
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
                    user_sector_permission
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
