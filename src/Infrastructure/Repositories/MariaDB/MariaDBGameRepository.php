<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\Game;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use Throwable;

class MariaDBGameRepository implements GameRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(Game $game): Game
    {
        try {                        
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new DatabaseTransactionCreationFailureException(
                    'Ocorreu um erro ao criar a transação!'
                );
            }

            $name = $game->getName();
            $isActive = intval(
                $game->getIsActive()
            );            

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    game (
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

            $wasInsertExecutionASuccess = $insertStatement->execute([
                ':name' => $name,
                ':isActive' => $isActive
            ]);

            if ($wasInsertExecutionASuccess === false) {
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
                    game 
                WHERE 
                    id = :id;'
            );

            if ($selectStatement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasSelectExecutionASuccess = $selectStatement->execute([
                ':id' => $lastInsertedId
            ]);

            if ($wasSelectExecutionASuccess === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $fetchResult = $selectStatement->fetch();

            if ($fetchResult === false) {
                throw new DatabaseFetchFailureException('Ocorreu uma falha ao realizar a busca!');
            }

            $this->pdo->commit();

            $game = new Game();
            $game->setId($fetchResult['id']);
            $game->setName($fetchResult['name']);
            $game->setIsActive(
                boolval($fetchResult['is_active'])
            );

            return $game;
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

    public function update(Game $game): bool
    {
        try {
            $id = $game->getId();
            $name = $game->getName();
            $isActive = intval(
                $game->getIsActive()
            );

            $statement = $this->pdo->prepare(
                'UPDATE 
                    game 
                SET 
                    name = :name, 
                    is_active = :isActive 
                WHERE 
                    id = :id;'
            );

            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasStatementExecutionSuccessful = $statement->execute([
                ':name' => $name,
                ':id' => $id,
                ':isActive' => $isActive
            ]);

            if ($wasStatementExecutionSuccessful === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $numberOfLinesAffected = $statement->rowCount();
            $wasSomeUpdateHappened = $numberOfLinesAffected > 0;
            return $wasSomeUpdateHappened;
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
                    game
                SET
                    is_active = :isActive
                WHERE
                    id = :id
                AND
                    is_active <> :isActive;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de atualização!'
                );
            }

            $wasTheUpdateSuccessfullyExecuted = $statement->execute([
                ':isActive' => $isActive,
                ':id' => $id
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

    public function findById(mixed $id): Game|null
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    game 
                WHERE 
                    id = :id;'
            );

            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
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

            $game = new Game();
            $game->setId($fetchResult['id']);
            $game->setName($fetchResult['name']);
            $game->setIsActive(
                boolval($fetchResult['is_active'])
            );

            return $game;
        } catch (
            DatabaseFetchFailureException |
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
                    game;'
            );

            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasTheStatementExecutionSuccessful = $statement->execute();

            if ($wasTheStatementExecutionSuccessful === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $fetchResult = $statement->fetchAll();

            if ($fetchResult === false) {
                return [];
            }

            $games = [];
            foreach ($fetchResult as $row) {
                $game = new Game();
                $game->setId($row['id']);
                $game->setName($row['name']);
                $game->setIsActive(
                    boolval($row['is_active'])
                );
                $games[] = $game;
            }

            return $games;
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
                    game 
                WHERE 
                    name = :name;'
            );

            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao tentar criar a declaração de busca!'
                );
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':name' => $name
            ]);

            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao tentar executar a declaração de busca!'
                );
            }

            $numberOfLinesAffected = $statement->rowCount();
            $hasDuplicatedNames = $numberOfLinesAffected > 0;

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
