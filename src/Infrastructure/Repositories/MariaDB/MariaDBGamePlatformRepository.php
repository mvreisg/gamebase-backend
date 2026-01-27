<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Domain\Data\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Data\GamePlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryUnexistantRegisterException;

class MariaDBGamePlatformRepository implements GamePlatformRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(GamePlatform $gamePlatform): GamePlatform
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBRepositoryTransactionCreationFailureException();
            }

            $platformId = $gamePlatform->getPlatformIdValue();
            $gameId = $gamePlatform->getGameIdValue();

            $insertStatement = $this->pdo->prepare(
                "INSERT INTO game_platform (
                    platform_id, 
                    game_id
                ) 
                VALUES (
                    :platformId, 
                    :gameId
                );"
            );
            if ($insertStatement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheInsertStatementExecutionSuccessful = $insertStatement->execute([
                ":platformId" => $platformId,
                ":gameId" => $gameId
            ]);
            if ($wasTheInsertStatementExecutionSuccessful === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $lastInsertedId = intval(
                $this->pdo->lastInsertId()
            );

            $selectStatement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    game_platform 
                WHERE 
                    id = :id;"
            );
            if ($selectStatement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
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

            $this->pdo->commit();

            return new GamePlatform(
                Id::make($fetchResult["id"]),
                Id::make($fetchResult["platform_id"]),
                Id::make($fetchResult["game_id"])
            );
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(GamePlatform $gamePlatform): bool
    {
        try {
            $id = $gamePlatform->getIdValue();
            $platformId = $gamePlatform->getPlatformIdValue();
            $gameId = $gamePlatform->getGameIdValue();

            $statement = $this->pdo->prepare(
                "UPDATE 
                    game_platform 
                SET 
                    platform_id = :platformId,
                    game_id = :gameId
                WHERE 
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ":platformId" => $platformId,
                ":gameId" => $gameId,
                ":id" => $id
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
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

            $statement = $this->pdo->prepare(
                "DELETE FROM
                    game_platform
                WHERE
                    id = :id"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementExecutionSuccessful = $statement->execute([
                "id" => $idValue,
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

    public function findById(Id $id): GamePlatform
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->pdo->prepare(
                "SELECT 
                    *
                FROM
                    game_platform
                WHERE
                    id = :id;"
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

            return new GamePlatform(
                Id::make($fetchResult["id"]),
                Id::make($fetchResult["platform_id"]),
                Id::make($fetchResult["game_id"])
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): GamePlatformCollection
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    game_platform;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementExecutionSuccessful = $statement->execute();
            if ($wasTheStatementExecutionSuccessful === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $result = $statement->fetchAll();
            if ($result === false) {
                return new GamePlatformCollection();
            }

            $gamePlatforms = new GamePlatformCollection();
            foreach ($result as $row) {
                $gamePlatforms->add(
                    new GamePlatform(
                        Id::make($row["id"]),
                        Id::make($row["platform_id"]),
                        Id::make($row["game_id"])
                    )
                );
            }
            return $gamePlatforms;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkIfExists(Id $id): void
    {
        try {
            $alias = "number_of_ids";
            $idValue = $id->getValue();

            $statement = $this->pdo->prepare(
                "SELECT
                    COUNT(*) 
                    AS
                    $alias
                FROM
                    game_platform
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
