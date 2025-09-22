<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use PDO;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatformEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBTransactionCreationFailureException;

class MariaDBGamePlatformEntityRepository implements GamePlatformEntityRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(GamePlatformEntity $gamePlatformEntity): GamePlatformEntity
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBTransactionCreationFailureException();
            }

            $platformId = $gamePlatformEntity->getPlatformId();
            $gameId = $gamePlatformEntity->getGameId();

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    game_platform 
                        (platform_id, game_id) 
                VALUES 
                    (:platformId, :gameId);'
            );
            if ($insertStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheInsertStatementExecutionSuccessful = $insertStatement->execute([
                ':platformId' => $platformId,
                ':gameId' => $gameId
            ]);
            if ($wasTheInsertStatementExecutionSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $lastInsertedId = $this->pdo->lastInsertId();
            $lastInsertedId = intval($lastInsertedId);

            $selectStatement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    game_platform 
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

            $gamePlatformEntity = new GamePlatformEntity(
                $fetchResult['id'],
                $fetchResult['platform_id'],
                $fetchResult['game_id']
            );

            return $gamePlatformEntity;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(GamePlatformEntity $gamePlatformEntity): bool
    {
        try {
            $id = $gamePlatformEntity->getId();
            $platformId = $gamePlatformEntity->getPlatformId();
            $gameId = $gamePlatformEntity->getGameId();

            $statement = $this->pdo->prepare(
                'UPDATE 
                    game_platform 
                SET 
                    platform_id = :platformId,
                    game_id = :gameId
                WHERE 
                    id = :id;'
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':platformId' => $platformId,
                ':gameId' => $gameId,
                ':id' => $id
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $numberOfAffectedLinesInTheRepository = $statement->rowCount();
            $wasTheRepositoryAffected = $numberOfAffectedLinesInTheRepository > 0;

            return $wasTheRepositoryAffected;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(GamePlatformEntity $gamePlatformEntity): bool
    {
        try {
            $id = $gamePlatformEntity->getId();

            $statement = $this->pdo->prepare(
                'DELETE FROM
                    game_platform
                WHERE
                    id = :id'
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementExecutionSuccessful = $statement->execute([
                'id' => $id,
            ]);
            if ($wasTheStatementExecutionSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $numberOfAffectedLinesInTheRepository = $statement->rowCount();
            $wasDeletionSuccessful = $numberOfAffectedLinesInTheRepository > 0;

            return $wasDeletionSuccessful;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): GamePlatformEntity|null
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    *
                FROM
                    game_platform
                WHERE
                    id = :id;'
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

            $result = $statement->fetch();
            if ($result === false) {
                return null;
            }

            $gamePlatformEntity = new GamePlatformEntity(
                $result['id'],
                $result['platform_id'],
                $result['game_id']
            );

            return $gamePlatformEntity;
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
                    game_platform;'
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
                $gamePlatformEntity = new GamePlatformEntity(
                    $row['id'],
                    $row['platform_id'],
                    $row['game_id']
                );

                $gamePlatforms[] = $gamePlatformEntity;
            }

            return $gamePlatforms;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
