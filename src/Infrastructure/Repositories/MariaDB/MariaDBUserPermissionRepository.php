<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use Throwable;

class MariaDBUserPermissionRepository implements UserPermissionRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(UserPermission $userPermission): UserPermission
    {
        $userId = $userPermission->getUserId();
        $permissionId = $userPermission->getPermissionId();

        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new DatabaseTransactionCreationFailureException(
                    'Ocorreu um erro ao criar a transação!'
                );
            }

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    user_permission 
                        (user_id, permission_id) 
                VALUES 
                    (:userId, :permissionId);'
            );
            if ($insertStatement === false) {
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de inserção!'
                );
            }

            $wasTheInsertStatementSuccessfullyExecuted = $insertStatement->execute([
                ':userId' => $userId,
                ':permissionId' => $permissionId
            ]);
            if ($wasTheInsertStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de inserção!'
                );
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
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de busca!'
                );
            }

            $wasTheSelectStatementSuccessfullyExecuted = $selectStatement->execute([
                ':id' => $lastInsertedId
            ]);
            if ($wasTheSelectStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $fetchResult = $selectStatement->fetch();
            if ($fetchResult === false) {
                throw new DatabaseFetchFailureException(
                    'Ocorreu um erro ao buscar os valores!'
                );
            }

            $this->pdo->commit();

            $userPermission = new UserPermission(
                $fetchResult['id'],
                $fetchResult['user_id'],
                $fetchResult['permission_id']
            );

            return $userPermission;
        } catch (
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException |
            Throwable $e
        ) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(UserPermission $userPermission): bool
    {
        try {
            $id = $userPermission->getId();
            $userId = $userPermission->getUserId();
            $permissionId = $userPermission->getPermissionId();            

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
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de atualização!'
                );
            }

            $wasTheStatementExecutionSuccessful = $statement->execute([
                ':id' => $id,
                ':userId' => $userId,
                ':permissionId' => $permissionId
            ]);
            if ($wasTheStatementExecutionSuccessful === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de atualização!'
                );
            }

            $numberOfRowsAffected = $statement->rowCount();
            $wasTheRepositoryAffected = $numberOfRowsAffected > 0;

            return $wasTheRepositoryAffected;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function delete(UserPermission $userPermission): bool
    {
        try {
            $statement = $this->pdo->prepare(
                'DELETE FROM
                    user_permission
                WHERE
                    id = :id;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de exclusão!'
                );
            }

            $id = $userPermission->getId();

            $wasTheDeleteStatementSuccessfullyExecuted = $statement->execute([
                ':id' => $id
            ]);
            if ($wasTheDeleteStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de exclusão!'
                );
            }

            $numberOfRowsAffected = $statement->rowCount();
            $wasTheDeleteSuccessful = $numberOfRowsAffected > 0;

            return $wasTheDeleteSuccessful;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function findById(int $id): UserPermission|null
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
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de busca!'
                );
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':id' => $id
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                return null;
            }

            $userPermission = new UserPermission(
                $fetchResult['id'],
                $fetchResult['user_id'],
                $fetchResult['permission_id']
            );

            return $userPermission;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
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
                    user_permission;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de busca!'
                );
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute();
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $fetchResult = $statement->fetchAll();
            if ($fetchResult === false) {
                return [];
            }

            $userPermissions = [];
            foreach ($fetchResult as $row) {
                $userPermission = new UserPermission(
                    $row['id'],
                    $row['user_id'],
                    $row['permission_id']
                );
                $userPermissions[] = $userPermission;
            }

            return $userPermissions;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }
}
