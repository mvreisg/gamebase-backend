<?php

declare(strict_types=1);

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
            PDOException $e
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
            $isActive = $permission->getIsActive();

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
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de atualização!'
                );
            }

            $wasTheUpdateSuccessful = $statement->execute([
                ':name' => $name,
                ':isActive' => $isActive,
                ':id' => $id
            ]);

            if ($wasTheUpdateSuccessful === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de atualização!'
                );
            }

            $numberOfLinesAffected = $statement->rowCount();
            $wasTheDatabaseAffected = $numberOfLinesAffected > 0;
            return $wasTheDatabaseAffected;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
                throw $e;
        }
    }

    public function findById(int $id): Permission|null
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
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de busca!'
                );
            }

            $wasTheFetchSuccessful = $statement->execute([
                ':id' => $id
            ]);

            if ($wasTheFetchSuccessful === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $result = $statement->fetch();

            if ($result === false) {
                return null;
            }

            $permission = new Permission();
            $permission->setId($result['id']);
            $permission->setName($result['name']);
            $permission->setIsActive($result['is_active']);

            return $permission;
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
                    permission;'
            );

            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de busca!'
                );
            }

            $wasTheSelectSuccessful = $statement->execute();
            if ($wasTheSelectSuccessful === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $result = $statement->fetchAll();

            if ($result === false) {
                return [];
            }

            $permissions = [];
            foreach ($result as $row) {
                $permission = new Permission();
                $permission->setId($row['id']);
                $permission->setName($row['name']);
                $permission->setIsActive($row['is_active']);
                $permissions[] = $permission;
            }

            return $permissions;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
                throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $isActive = (int)$isActive;

            $statement = $this->pdo->prepare(
                'UPDATE
                    permission
                SET
                    is_active = :isActive
                WHERE
                    id = :id;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de atualização!'
                );
            }

            $wasTheUpdateSuccessfullyExecuted = $statement->execute([
                ':id' => $id,
                ':isActive' => $isActive
            ]);
            if ($wasTheUpdateSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de atualização!'
                );
            }

            $wasTheUpdateOcurred = $statement->rowCount() > 0;
            return $wasTheUpdateOcurred;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function hasDuplicatedNames(string $name): bool
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
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de busca!'
                );
            }

            $wasTheStatementExecutedSuccessfully = $statement->execute([
                ':name' => $name
            ]);

            if ($wasTheStatementExecutedSuccessfully === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $numberOfAffectedLines = $statement->rowCount();
            $hasDuplicatedNames = $numberOfAffectedLines > 0;

            return $hasDuplicatedNames;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }
}
