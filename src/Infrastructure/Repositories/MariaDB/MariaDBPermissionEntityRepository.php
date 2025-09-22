<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Domain\Entities\PermissionEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBTransactionCreationFailureException;
use PDO;

class MariaDBPermissionEntityRepository implements PermissionEntityRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(PermissionEntity $permissionEntity): PermissionEntity
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBTransactionCreationFailureException();
            }

            $name = $permissionEntity->getName();
            $isActive = intval(
                $permissionEntity->getIsActive()
            );

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    permission 
                (
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

            $wasTheInsertSuccessful = $insertStatement->execute([
                ':name' => $name,
                ':isActive' => $isActive
            ]);

            if ($wasTheInsertSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $lastInsertedId = $this->pdo->lastInsertId();

            $selectStatement = $this->pdo->prepare(
                'SELECT 
                    *
                FROM
                    permission
                WHERE
                    id = :id;'
            );

            if ($selectStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheSelectSuccessful = $selectStatement->execute([
                ':id' => $lastInsertedId
            ]);

            if ($wasTheSelectSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $selectStatement->fetch();

            if ($fetchResult === false) {
                throw new MariaDBFetchFailureException();
            }

            $this->pdo->commit();

            $permissionEntity = new PermissionEntity(
                $fetchResult['id'],
                $fetchResult['name'],
                boolval(
                    $fetchResult['is_active']
                )
            );

            return $permissionEntity;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(PermissionEntity $permissionEntity): bool
    {
        try {
            $id = $permissionEntity->getId();
            $name = $permissionEntity->getName();
            $isActive = intval(
                $permissionEntity->getIsActive()
            );

            $statement = $this->pdo->prepare(
                'UPDATE
                    permission
                SET
                    name = :name,
                    is_active = :isActive
                WHERE
                    id = :id;'
            );

            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheUpdateSuccessful = $statement->execute([
                ':name' => $name,
                ':isActive' => $isActive,
                ':id' => $id
            ]);

            if ($wasTheUpdateSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $numberOfLinesAffected = $statement->rowCount();
            $wasTheRepositoryAffected = $numberOfLinesAffected > 0;
            return $wasTheRepositoryAffected;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $isActive = intval($isActive);

            $statement = $this->pdo->prepare(
                'UPDATE
                    permission
                SET
                    is_active = :isActive
                WHERE
                    id = :id;'
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheUpdateSuccessfullyExecuted = $statement->execute([
                ':id' => $id,
                ':isActive' => $isActive
            ]);
            if ($wasTheUpdateSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $wasTheUpdateOcurred = $statement->rowCount() > 0;
            return $wasTheUpdateOcurred;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): PermissionEntity|null
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM
                    permission
                WHERE
                    id = :id;'
            );

            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheFetchSuccessful = $statement->execute([
                ':id' => $id
            ]);

            if ($wasTheFetchSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();

            if ($fetchResult === false) {
                return null;
            }

            $permissionEntity = new PermissionEntity(
                $fetchResult['id'],
                $fetchResult['name'],
                boolval(
                    $fetchResult['is_active']
                )
            );

            return $permissionEntity;
        } catch (\Throwable $e) {
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
                    permission;'
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

            $permissionEntities = [];
            foreach ($fetchResult as $row) {
                $permissionEntity = new PermissionEntity(
                    $row['id'],
                    $row['name'],
                    boolval(
                        $row['is_active']
                    )
                );
                $permissionEntities[] = $permissionEntity;
            }

            return $permissionEntities;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkDuplicatedNames(string $name): void
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    permission 
                WHERE 
                    name = :name;'
            );

            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementExecutedSuccessfully = $statement->execute([
                ':name' => $name
            ]);

            if ($wasTheStatementExecutedSuccessfully === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $numberOfAffectedLines = $statement->rowCount();
            if ($numberOfAffectedLines > 0) {
                throw new MariaDBDuplicatedEntryException(
                    $name
                );
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
