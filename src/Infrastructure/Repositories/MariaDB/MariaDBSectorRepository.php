<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Domain\Entities\Sector;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use PDO;
use PDOException;
use Throwable;

class MariaDBSectorRepository implements SectorRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(Sector $sector): Sector
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new DatabaseTransactionCreationFailureException(
                    'Ocorreu um erro ao criar a transação!'
                );
            }

            $name = $sector->getName();
            $isActive = intval(
                $sector->getIsActive()
            );            

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    sector 
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
                    sector
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

            $fetchResult = $selectStatement->fetch();

            if ($fetchResult === false) {
                throw new DatabaseFetchFailureException(
                    'Ocorreu um erro ao realizar a busca!'
                );
            }

            $this->pdo->commit();

            $sector = new Sector();
            $sector->setId($fetchResult['id']);
            $sector->setName($fetchResult['name']);
            $sector->setIsActive(
                boolval($fetchResult['is_active'])
            );

            return $sector;
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

    public function update(Sector $sector): bool
    {
        try {
            $id = $sector->getId();
            $name = $sector->getName();
            $isActive = intval(
                $sector->getIsActive()
            );

            $statement = $this->pdo->prepare(
                'UPDATE
                    sector
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
            PDOException | 
            Throwable $e
        ) {
                throw $e;
        }
    }

    public function findById(int $id): Sector|null
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM
                    sector
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

            $fetchResult = $statement->fetch();

            if ($fetchResult === false) {
                return null;
            }

            $sector = new Sector();
            $sector->setId($fetchResult['id']);
            $sector->setName($fetchResult['name']);
            $sector->setIsActive(
                boolval($fetchResult['is_active'])
            );

            return $sector;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException | 
            Throwable $e
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
                    sector;'
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

            $fetchResult = $statement->fetchAll();

            if ($fetchResult === false) {
                return [];
            }

            $sectors = [];
            foreach ($fetchResult as $row) {
                $sector = new Sector();
                $sector->setId($row['id']);
                $sector->setName($row['name']);
                $sector->setIsActive(
                    boolval($row['is_active'])
                );
                $sectors[] = $sector;
            }

            return $sectors;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException | 
            Throwable $e
        ) {
                throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $isActive = intval($isActive);

            $statement = $this->pdo->prepare(
                'UPDATE
                    sector
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
            PDOException | 
            Throwable $e
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
                    sector 
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
