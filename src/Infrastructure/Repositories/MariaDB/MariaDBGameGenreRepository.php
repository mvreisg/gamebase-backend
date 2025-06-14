<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use Throwable;

class MariaDBGameGenreRepository implements GameGenreRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(GameGenre $gameGenre): GameGenre
    {
        $genreId = $gameGenre->getGenreId();
        $gameId = $gameGenre->getGameId();

        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new DatabaseTransactionCreationFailureException('Ocorreu um erro ao criar a transação!');
            }

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    game_genre 
                        (genre_id, game_id) 
                VALUES 
                    (:genreId, :gameId);'
            );
            if ($insertStatement === false) {
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de inserção!'
                );
            }

            $wasTheInsertStatementSuccessfullyExecuted = $insertStatement->execute([
                ':genreId' => $genreId,
                ':gameId' => $gameId
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
                    game_genre 
                WHERE 
                    id = :id;'
            );
            if ($selectStatement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
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
                throw new DatabaseFetchFailureException('Ocorreu um erro ao buscar os valores!');
            }

            $this->pdo->commit();

            $gameGenre = new GameGenre();
            $gameGenre->setId($fetchResult['id']);
            $gameGenre->setGenreId($fetchResult['genre_id']);
            $gameGenre->setGameId($fetchResult['game_id']);

            return $gameGenre;
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

    public function update(GameGenre $gameGenre): bool
    {
        try {
            $id = $gameGenre->getId();
            $gameId = $gameGenre->getGameId();
            $genreId = $gameGenre->getGenreId();

            $statement = $this->pdo->prepare(
                'UPDATE 
                    game_genre 
                SET 
                    genre_id = :genreId, 
                    game_id = :gameId 
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
                ':gameId' => $gameId,
                ':genreId' => $genreId
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

    public function delete(GameGenre $gameGenre): bool
    {
        try {
            $statement = $this->pdo->prepare(
                'DELETE FROM
                    game_genre
                WHERE
                    id = :id;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de exclusão!'
                );
            }

            $id = $gameGenre->getId();

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

    public function findById(int $id): GameGenre|null
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    game_genre 
                WHERE 
                    id = :id'
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

            $gameGenre = new GameGenre();
            $gameGenre->setId($fetchResult['id']);
            $gameGenre->setGameId($fetchResult['game_id']);
            $gameGenre->setGenreId($fetchResult['genre_id']);

            return $gameGenre;
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
                    game_genre'
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

            $fetchResult = $statement->fetchAll();
            if ($fetchResult === false) {
                return [];
            }

            $gameGenres = [];

            foreach ($fetchResult as $row) {
                $gameGenre = new GameGenre();
                $gameGenre->setId($row['id']);
                $gameGenre->setGameId($row['game_id']);
                $gameGenre->setGenreId($row['genre_id']);

                $gameGenres[] = $gameGenre;
            }

            return $gameGenres;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }
}
