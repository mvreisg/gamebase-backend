<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\Genre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;

class MariaDBGenreRepository implements GenreRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(Genre $genre): Genre
    {
        try {
            $this->pdo->beginTransaction();

            $name = $genre->getName();
            $isActive = (int)$genre->getIsActive();

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    genre (
                        name,
                        is_active
                    ) 
                VALUES 
                    (
                        :name,
                        :isActive
                    );'
            );

            if ($insertStatement === false) {
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de inserção!'
                );
            }

            $wasInsertStatementExecutedSuccessfully = $insertStatement->execute([
                ':name' => $name,
                ':isActive' => $isActive
            ]);

            if ($wasInsertStatementExecutedSuccessfully === false) {
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
                    genre 
                WHERE 
                    id = :id;'
            );

            if ($selectStatement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasSelectStatementSuccessfullyExecuted = $selectStatement->execute([
                ':id' => $lastInsertedId
            ]);

            if ($wasSelectStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao tentar executar a declaração de busca!'
                );
            }

            $fetchResult = $selectStatement->fetch();

            if ($fetchResult === false) {
                throw new DatabaseFetchFailureException('Ocorreu um erro ao buscar os dados do gênero!');
            }

            $this->pdo->commit();

            $genre = new Genre();
            $genre->setId($fetchResult['id']);
            $genre->setName($fetchResult['name']);
            $genre->setIsActive($fetchResult['is_active']);

            return $genre;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException $e
        ) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(Genre $genre): bool
    {
        $id = $genre->getId();
        $name = $genre->getName();
        $isActive = (int)$genre->getIsActive();

        try {
            $statement = $this->pdo->prepare(
                'UPDATE 
                    genre 
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

            $wasTheUpdateSuccessfullyExecuted = $statement->execute([
                ':name' => $name,
                ':id' => $id,
                ':isActive' => $isActive
            ]);
            if ($wasTheUpdateSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de atualização!'
                );
            }

            $numberOfAffectedLines = $statement->rowCount();
            $wasTheRepositoryAffected = $numberOfAffectedLines > 0;

            return $wasTheRepositoryAffected;
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
                    genre
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

    public function findById(int $id): Genre|null
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    genre 
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

            $result = $statement->fetch();
            if ($result === false) {
                return null;
            }

            $genre = new Genre();
            $genre->setId($result['id']);
            $genre->setName($result['name']);
            $genre->setIsActive($result['is_active']);

            return $genre;
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
                    genre;'
            );

            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute();

            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $result = $statement->fetchAll();

            if ($result === false) {
                return [];
            }

            $genres = [];

            foreach ($result as $row) {
                $genre = new Genre();
                $genre->setId($row['id']);
                $genre->setName($row['name']);
                $genre->setIsActive($row['is_active']);
                $genres[] = $genre;
            }

            return $genres;
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
                    genre 
                WHERE 
                    name = :name;'
            );

            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
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
