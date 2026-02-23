<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Domain\Data\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Data\GameGenreCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo\PdoRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryUnexistantRegisterException;

class MariaDBGameGenreRepository implements GameGenreRepositoryInterface
{
    private PdoRepositoryConnection $connection;

    public function __construct(PdoRepositoryConnection $connection)
    {
        $this->connection = $connection;
    }

    public function insert(GameGenre $gameGenre): GameGenre
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->connection->get()->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBRepositoryTransactionCreationFailureException();
            }

            $genreId = $gameGenre->getGenreIdValue();
            $gameId = $gameGenre->getGameIdValue();

            $insertStatement = $this->connection->get()->prepare(
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
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheInsertStatementSuccessfullyExecuted = $insertStatement->execute([
                ":genreId" => $genreId,
                ":gameId" => $gameId
            ]);
            if ($wasTheInsertStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $lastInsertedId = intval(
                $this->connection->get()->lastInsertId()
            );

            $selectStatement = $this->connection->get()->prepare(
                "SELECT 
                    * 
                FROM 
                    game_genre 
                WHERE 
                    id = :id;"
            );
            if ($selectStatement === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $wasTheSelectStatementSuccessfullyExecuted = $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);
            if ($wasTheSelectStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $selectStatement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBRepositoryStatementFetchFailureException();
            }

            $this->connection->get()->commit();

            $return = new GameGenre(
                Id::make($fetchResult["game_id"]),
                Id::make($fetchResult["genre_id"])
            );
            $return->setId(Id::make($fetchResult["id"]));
            return $return;
        } catch (\Throwable $e) {
            $this->connection->get()->rollBack();
            throw $e;
        }
    }

    public function update(GameGenre $gameGenre): bool
    {
        try {
            $id = $gameGenre->getIdValue();
            $gameId = $gameGenre->getGameIdValue();
            $genreId = $gameGenre->getGenreIdValue();

            $statement = $this->connection->get()->prepare(
                "UPDATE 
                    game_genre 
                SET 
                    genre_id = :genreId, 
                    game_id = :gameId 
                WHERE 
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementExecutionSuccessful = $statement->execute([
                ":id" => $id,
                ":gameId" => $gameId,
                ":genreId" => $genreId
            ]);
            if ($wasTheStatementExecutionSuccessful === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(Id $id): bool
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->connection->get()->prepare(
                "DELETE FROM
                    game_genre
                WHERE
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheDeleteStatementSuccessfullyExecuted = $statement->execute([
                ":id" => $idValue
            ]);
            if ($wasTheDeleteStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): GameGenre
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->connection->get()->prepare(
                "SELECT 
                    * 
                FROM 
                    game_genre 
                WHERE 
                    id = :id"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ":id" => $idValue
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBRepositoryUnexistantRegisterException(
                    $idValue
                );
            }

            $return = new GameGenre(
                Id::make($fetchResult["game_id"]),
                Id::make($fetchResult["genre_id"])
            );
            $return->setId(Id::make($fetchResult["id"]));
            return $return;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): GameGenreCollection
    {
        try {
            $statement = $this->connection->get()->prepare(
                "SELECT 
                    * 
                FROM 
                    game_genre"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute();
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetchAll();
            if ($fetchResult === false) {
                return new GameGenreCollection();
            }

            $gameGenres = new GameGenreCollection();
            foreach ($fetchResult as $row) {
                $value = new GameGenre(
                    Id::make($row["game_id"]),
                    Id::make($row["genre_id"])
                );
                $value->setId(Id::make($row["id"]));
                $gameGenres->add($value);
            }

            return $gameGenres;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkIfExists(Id $id): void
    {
        try {
            $alias = "number_of_ids";
            $idValue = $id->getValue();

            $statement = $this->connection->get()->prepare(
                "SELECT
                    COUNT(*) 
                    AS
                    $alias
                FROM
                    game_genre
                WHERE
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheCheckSuccessfullyExecuted = $statement->execute([
                ":id" => $idValue
            ]);
            if ($wasTheCheckSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            $numberOfIds = intval(
                $fetchResult[
                    $alias
                ]
            );

            if ($numberOfIds === 0) {
                throw new MariaDBRepositoryUnexistantRegisterException(
                    $idValue
                );
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
