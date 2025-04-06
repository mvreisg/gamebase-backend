<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Domain\Entities\Permission;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
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
            $name = $permission->getName();
            $isActive = $permission->getIsActive();

            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();

            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new DatabaseTransactionCreationFailureException(
                    'Ocorreu um erro ao criar a transação!'
                );
            }

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
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de inserção!'
                );
            }

            $wasTheInsertSuccessful = $insertStatement->execute([
                ':name' => $name,
                ':isActive' => $isActive
            ]);

            if ($wasTheInsertSuccessful === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de inserção!'
                );
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
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de busca!'
                );
            }

            $wasTheSelectSuccessful = $selectStatement->execute([
                ':id' => $lastInsertedId
            ]);

            if ($wasTheSelectSuccessful === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $result = $selectStatement->fetchAll();

            if ($result === false) {
                throw new DatabaseFetchFailureException(
                    'Ocorreu um erro ao realizar a busca!'
                );
            }

            $this->pdo->commit();

            $permission = new Permission();
            $permission->setId($result['id']);
            $permission->setName($result['name']);
            $permission->setIsActive($result['is_active']);

            return $permission;
        } catch (
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException $e) {
                $this->pdo->rollBack();
                throw $e;
            }
    }

    public function update(Permission $permission): bool
    {
        try {
            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function findById(int $id): Permission|null
    {
        try {
            return null;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            return [];
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function setIsActive(bool $isActive): bool
    {
        try {
            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function hasDuplicatedNames(string $name): bool
    {
        try {
            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
