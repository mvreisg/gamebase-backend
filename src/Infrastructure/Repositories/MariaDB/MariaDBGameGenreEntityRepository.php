<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenreEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBTransactionCreationFailureException;
use PDO;

class MariaDBGameGenreEntityRepository implements GameGenreEntityRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(GameGenreEntity $gameGenreEntity): GameGenreEntity
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBTransactionCreationFailureException();
            }

            $genreId = $gameGenreEntity->getGenreId();
            $gameId = $gameGenreEntity->getGameId();

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    game_genre 
                        (genre_id, game_id) 
                VALUES 
                    (:genreId, :gameId);'
            );
            if ($insertStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheInsertStatementSuccessfullyExecuted = $insertStatement->execute([
                ':genreId' => $genreId,
                ':gameId' => $gameId
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
                    game_genre 
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

            $gameGenreEntity = new GameGenreEntity(
                $fetchResult['id'],
                $fetchResult['genre_id'],
                $fetchResult['game_id']
            );

            return $gameGenreEntity;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(GameGenreEntity $gameGenreEntity): bool
    {
        try {
            $id = $gameGenreEntity->getId();
            $gameId = $gameGenreEntity->getGameId();
            $genreId = $gameGenreEntity->getGenreId();

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
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementExecutionSuccessful = $statement->execute([
                ':id' => $id,
                ':gameId' => $gameId,
                ':genreId' => $genreId
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

    public function delete(GameGenreEntity $gameGenreEntity): bool
    {
        try {
            $statement = $this->pdo->prepare(
                'DELETE FROM
                    game_genre
                WHERE
                    id = :id;'
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $id = $gameGenreEntity->getId();

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

    public function findById(int $id): GameGenreEntity|null
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

            $gameGenreEntity = new GameGenreEntity(
                $fetchResult['id'],
                $fetchResult['genre_id'],
                $fetchResult['game_id']
            );

            return $gameGenreEntity;
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
                    game_genre'
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

            $gameGenreEntities = [];

            foreach ($fetchResult as $row) {
                $gameGenreEntity = new GameGenreEntity(
                    $row['id'],
                    $row['genre_id'],
                    $row['game_id']
                );

                $gameGenreEntities[] = $gameGenreEntity;
            }

            return $gameGenreEntities;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
