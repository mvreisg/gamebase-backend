<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBUnexistantRegisterException;
use PDO;
use PDOException;

class MariaDBGameGenreRepository implements GameGenreRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(GameGenre $gameGenre): GameGenre
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBTransactionCreationFailureException();
            }

            $genreId = $gameGenre->getGenreId();
            $gameId = $gameGenre->getGameId();

            $insertStatement = $this->pdo->prepare(
                "INSERT INTO game_genre (
                    genre_id, 
                    game_id
                ) 
                VALUES (
                    :genreId, 
                    :gameId
                );"
            );
            if ($insertStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheInsertStatementSuccessfullyExecuted = $insertStatement->execute([
                ":genreId" => $genreId,
                ":gameId" => $gameId
            ]);
            if ($wasTheInsertStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $lastInsertedId = intval(
                $this->pdo->lastInsertId()
            );

            $selectStatement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    game_genre 
                WHERE 
                    id = :id;"
            );
            if ($selectStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheSelectStatementSuccessfullyExecuted = $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);
            if ($wasTheSelectStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $selectStatement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBFetchFailureException();
            }

            $this->pdo->commit();

            return new GameGenre(
                $fetchResult["id"],
                $fetchResult["genre_id"],
                $fetchResult["game_id"]
            );
        } catch (
            MariaDBTransactionCreationFailureException |
            MariaDBStatementCreationFailureException |
            MariaDBStatementExecutionFailureException |
            MariaDBFetchFailureException |
            PDOException |
            \Throwable
            $e
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
                "UPDATE 
                    game_genre 
                SET 
                    genre_id = :genreId, 
                    game_id = :gameId 
                WHERE 
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementExecutionSuccessful = $statement->execute([
                ":id" => $id,
                ":gameId" => $gameId,
                ":genreId" => $genreId
            ]);
            if ($wasTheStatementExecutionSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (
            MariaDBStatementCreationFailureException |
            MariaDBStatementExecutionFailureException |
            PDOException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function delete(GameGenre $gameGenre): bool
    {
        try {
            $statement = $this->pdo->prepare(
                "DELETE FROM
                    game_genre
                WHERE
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $id = $gameGenre->getId();

            $wasTheDeleteStatementSuccessfullyExecuted = $statement->execute([
                ":id" => $id
            ]);
            if ($wasTheDeleteStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (
            MariaDBStatementCreationFailureException |
            MariaDBStatementExecutionFailureException |
            PDOException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function findById(int $id): GameGenre
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    game_genre 
                WHERE 
                    id = :id"
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ":id" => $id
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBUnexistantRegisterException(
                    "Unexistant register with the id $id."
                );
            }

            return new GameGenre(
                $fetchResult["id"],
                $fetchResult["genre_id"],
                $fetchResult["game_id"]
            );
        } catch (
            MariaDBStatementCreationFailureException |
            MariaDBStatementExecutionFailureException |
            MariaDBUnexistantRegisterException |
            PDOException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    game_genre"
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

            $gameGenres = [];

            foreach ($fetchResult as $row) {
                $gameGenres[] = new GameGenre(
                    $row["id"],
                    $row["genre_id"],
                    $row["game_id"]
                );
            }

            return $gameGenres;
        } catch (
            MariaDBStatementCreationFailureException |
            MariaDBStatementExecutionFailureException |
            PDOException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function checkIfExists(int $id): void
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT
                    COUNT(*) 
                    AS
                    number
                FROM
                    game_genre
                WHERE
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheCheckSuccessfullyExecuted = $statement->execute([
                ":id" => $id
            ]);
            if ($wasTheCheckSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            $numberOfIds = intval(
                $fetchResult["number"]
            );

            if ($numberOfIds === 0) {
                throw new MariaDBUnexistantRegisterException(
                    "Unexistant register with the id $id."
                );
            }
        } catch (
            MariaDBStatementCreationFailureException |
            MariaDBStatementExecutionFailureException |
            PDOException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }
}
