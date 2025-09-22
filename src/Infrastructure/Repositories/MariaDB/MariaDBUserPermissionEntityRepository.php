<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use PDO;
use Mvreisg\GamebaseBackend\Domain\Entities\UserPermissionEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserPermissionEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBTransactionCreationFailureException;

class MariaDBUserPermissionEntityRepository implements UserPermissionEntityRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(UserPermissionEntity $userPermissionEntity): UserPermissionEntity
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBTransactionCreationFailureException();
            }

            $userId = $userPermissionEntity->getUserId();
            $permissionId = $userPermissionEntity->getPermissionId();

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    user_permission 
                        (user_id, permission_id) 
                VALUES 
                    (:userId, :permissionId);'
            );
            if ($insertStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheInsertStatementSuccessfullyExecuted = $insertStatement->execute([
                ':userId' => $userId,
                ':permissionId' => $permissionId
            ]);
            if ($wasTheInsertStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $lastInsertedId = $this->pdo->lastInsertId();
            $lastInsertedId = intval($lastInsertedId);

            $selectStatement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    user_permission 
                WHERE 
                    id = :id;'
            );
            if ($selectStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheSelectStatementSuccessfullyExecuted = $selectStatement->execute([
                ':id' => $lastInsertedId
            ]);
            if ($wasTheSelectStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $selectStatement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBFetchFailureException();
            }

            $this->pdo->commit();

            $userPermissionEntity = new UserPermissionEntity(
                $fetchResult['id'],
                $fetchResult['user_id'],
                $fetchResult['permission_id']
            );

            return $userPermissionEntity;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(UserPermissionEntity $userPermissionEntity): bool
    {
        try {
            $id = $userPermissionEntity->getId();
            $userId = $userPermissionEntity->getUserId();
            $permissionId = $userPermissionEntity->getPermissionId();

            $statement = $this->pdo->prepare(
                'UPDATE 
                    user_permission 
                SET 
                    user_id = :userId, 
                    permission_id = :permissionId 
                WHERE 
                    id = :id;'
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementExecutionSuccessful = $statement->execute([
                ':id' => $id,
                ':userId' => $userId,
                ':permissionId' => $permissionId
            ]);
            if ($wasTheStatementExecutionSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $numberOfRowsAffected = $statement->rowCount();
            $wasTheRepositoryAffected = $numberOfRowsAffected > 0;

            return $wasTheRepositoryAffected;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(UserPermissionEntity $userPermissionEntity): bool
    {
        try {
            $statement = $this->pdo->prepare(
                'DELETE FROM
                    user_permission
                WHERE
                    id = :id;'
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $id = $userPermissionEntity->getId();

            $wasTheDeleteStatementSuccessfullyExecuted = $statement->execute([
                ':id' => $id
            ]);
            if ($wasTheDeleteStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $numberOfRowsAffected = $statement->rowCount();
            $wasTheDeleteSuccessful = $numberOfRowsAffected > 0;

            return $wasTheDeleteSuccessful;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): UserPermissionEntity|null
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    user_permission 
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
                return null;
            }

            $userPermissionEntity = new UserPermissionEntity(
                $fetchResult['id'],
                $fetchResult['user_id'],
                $fetchResult['permission_id']
            );

            return $userPermissionEntity;
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
                    user_permission;'
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

            $userPermissionEntities = [];
            foreach ($fetchResult as $row) {
                $userPermissionEntity = new UserPermissionEntity(
                    $row['id'],
                    $row['user_id'],
                    $row['permission_id']
                );

                $userPermissionEntities[] = $userPermissionEntity;
            }

            return $userPermissionEntities;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
