<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use PDO;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBUnexistantRegisterException;
use PDOException;

class MariaDBGamePlatformRepository implements GamePlatformRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(GamePlatform $gamePlatform): GamePlatform
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBTransactionCreationFailureException();
            }

            $platformId = $gamePlatform->getPlatformId();
            $gameId = $gamePlatform->getGameId();

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
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheInsertStatementExecutionSuccessful = $insertStatement->execute([
                ":platformId" => $platformId,
                ":gameId" => $gameId
            ]);
            if ($wasTheInsertStatementExecutionSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
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

            return new GamePlatform(
                $fetchResult["id"],
                $fetchResult["platform_id"],
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

    public function update(GamePlatform $gamePlatform): bool
    {
        try {
            $id = $gamePlatform->getId();
            $platformId = $gamePlatform->getPlatformId();
            $gameId = $gamePlatform->getGameId();

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
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ":platformId" => $platformId,
                ":gameId" => $gameId,
                ":id" => $id
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
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

    public function delete(GamePlatform $gamePlatform): bool
    {
        try {
            $id = $gamePlatform->getId();

            $statement = $this->pdo->prepare(
                "DELETE FROM
                    game_platform
                WHERE
                    id = :id"
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementExecutionSuccessful = $statement->execute([
                "id" => $id,
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

    public function findById(int $id): GamePlatform
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT 
                    *
                FROM
                    game_platform
                WHERE
                    id = :id;"
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

            return new GamePlatform(
                $fetchResult["id"],
                $fetchResult["platform_id"],
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
                    game_platform;"
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementExecutionSuccessful = $statement->execute();
            if ($wasTheStatementExecutionSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $result = $statement->fetchAll();
            if ($result === false) {
                return [];
            }

            $gamePlatforms = [];

            foreach ($result as $row) {
                $gamePlatform = new GamePlatform(
                    $row["id"],
                    $row["platform_id"],
                    $row["game_id"]
                );

                $gamePlatforms[] = $gamePlatform;
            }

            return $gamePlatforms;
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
                    game_platform
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
