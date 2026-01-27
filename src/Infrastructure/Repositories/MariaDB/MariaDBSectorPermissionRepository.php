<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermission;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryUnexistantRegisterException;

class MariaDBSectorPermissionRepository implements SectorPermissionRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(SectorPermission $sectorPermission): SectorPermission
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBRepositoryTransactionCreationFailureException();
            }

            $sectorId = $sectorPermission->getSectorIdValue();
            $permissionId = $sectorPermission->getPermissionIdValue();

            $insertStatement = $this->pdo->prepare(
                "INSERT INTO sector_permission (
                    sector_id, 
                    permission_id
                ) 
                VALUES (
                    :sectorId, 
                    :permissionId
                );"
            );
            if ($insertStatement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheInsertStatementSuccessfullyExecuted = $insertStatement->execute([
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
                    sector_permission 
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

            return new SectorPermission(
                Id::make($fetchResult["id"]),
                Id::make($fetchResult["sector_id"]),
                Id::make($fetchResult["permission_id"])
            );
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(SectorPermission $sectorPermission): bool
    {
        try {
            $id = $sectorPermission->getIdValue();
            $sectorId = $sectorPermission->getSectorIdValue();
            $permissionId = $sectorPermission->getPermissionIdValue();

            $statement = $this->pdo->prepare(
                "UPDATE 
                    sector_permission 
                SET 
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
                    sector_permission
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

    public function findById(Id $id): SectorPermission
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    sector_permission 
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

            return new SectorPermission(
                Id::make($fetchResult["id"]),
                Id::make($fetchResult["sector_id"]),
                Id::make($fetchResult["permission_id"])
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAllByPermissionId(Id $permissionId): SectorPermissionCollection
    {
        try {
            $permissionIdValue = $permissionId->getValue();

            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    sector_permission
                WHERE
                    permission_id = :permissionId;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ":permissionId" => $permissionIdValue
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetchAll();
            if ($fetchResult === false) {
                return new SectorPermissionCollection();
            }

            $sectorPermissions = new SectorPermissionCollection();
            foreach ($fetchResult as $row) {
                $sectorPermissions->add(
                    new SectorPermission(
                        Id::make($row["id"]),
                        Id::make($row["sector_id"]),
                        Id::make($row["permission_id"])
                    )
                );
            }
            return $sectorPermissions;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): SectorPermissionCollection
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    sector_permission;"
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
                return new SectorPermissionCollection();
            }

            $sectorPermissions = new SectorPermissionCollection();
            foreach ($fetchResult as $row) {
                $sectorPermissions->add(
                    new SectorPermission(
                        Id::make($row["id"]),
                        Id::make($row["sector_id"]),
                        Id::make($row["permission_id"])
                    )
                );
            }
            return $sectorPermissions;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkIfExists(Id $id): void
    {
        try {
            $alias = "number_of_ids";
            $idValue = $id->getValue();

            $statement = $this->pdo->prepare(
                "SELECT
                    COUNT(*) 
                    AS
                    $alias
                FROM
                    sector_permission
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
