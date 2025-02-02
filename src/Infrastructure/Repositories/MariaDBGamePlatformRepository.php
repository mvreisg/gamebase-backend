<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformRepositoryInterface;

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
            $statement = $this->pdo->prepare('INSERT INTO game_platform (platform_id, game_id) VALUES (:platformId, :gameId);');
            $statement->execute([
                ':platformId' => $gamePlatform->getPlatformId(),
                ':gameId' => $gamePlatform->getGameId()
            ]);

            $lastInsertId = intval($this->pdo->lastInsertId());
            $statement = $this->pdo->prepare('SELECT * FROM game_platform WHERE id = :id;');
            $statement->execute([
                ':id' => $lastInsertId
            ]);
            $result = $statement->fetch();

            $gamePlatform = new GamePlatform();
            $gamePlatform->setId($result['id']);
            $gamePlatform->setPlatformId($result['platform_id']);
            $gamePlatform->setGameId($result['game_id']);

            return $gamePlatform;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function edit(GamePlatform $gamePlatform): bool
    {
        try {
            $gameId = $gamePlatform->getGameId();
            $statement = $this->pdo->prepare('UPDATE game_platform SET platform_id WHERE game_id = :gameId;');
            $wasItSuccessful = $statement->execute([':gameId' => $gameId]);
            return $wasItSuccessful;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function delete(GamePlatform $gamePlatform): bool
    {
        try {
            $statement = $this->pdo->prepare(
                'DELETE FROM
                        game_platform
                    WHERE
                        game_id = :gameId
                    AND
                        platform_id = :platformId;'
            );

            $gameId = $gamePlatform->getGameId();
            $platformId = $gamePlatform->getPlatformId();

            $wasItSuccessful = $statement->execute([
                'gameId' => $gameId,
                'platformId' => $platformId
            ]);

            return $wasItSuccessful;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function deleteAllByGameId(GamePlatform $gamePlatform): bool
    {
        try {
            $statement = $this->pdo->prepare(
                'DELETE FROM
                        game_platform
                    WHERE
                        game_id = :gameId;'
            );

            $gameId = $gamePlatform->getGameId();

            $wasItSuccessful = $statement->execute(['gameId' => $gameId]);
            return $wasItSuccessful;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function findAllGamePlatformsByGameId(int $gameId): array
    {
        try {
            $statement = $this->pdo->prepare('SELECT * FROM game_platform WHERE game_id = :gameId;');
            $statement->execute([':gameId' => $gameId]);

            $result = $statement->fetchAll();
            $gamePlatforms = [];
            foreach ($result as $row) {
                $gamePlatform = new GamePlatform();
                $gamePlatform->setId($row['id']);
                $gamePlatform->setGameId($row['game_id']);
                $gamePlatform->setPlatformId($row['platform_id']);
                $gamePlatforms[] = $gamePlatform;
            }

            return $gamePlatforms;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function innerJoinBetweenGameAndGamePlatformByGameId(): array
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT
                        game_platform.id AS id,
                        game_platform.game_id AS game_id,
                        game_platform.platform_id AS platform_id
                    FROM
                        game
                    INNER JOIN
                        game_platform
                    ON
                        game.id = game_platform.game_id;'
            );
            $statement->execute();
            $result = $statement->fetchAll();

            $gamePlatforms = [];
            foreach ($result as $row) {
                $gamePlatform = new GamePlatform();
                $gamePlatform->setId($row['id']);
                $gamePlatform->setGameId($row['game_id']);
                $gamePlatform->setPlatformId($row['platform_id']);
                $gamePlatforms[] = $gamePlatform;
            }

            return $gamePlatforms;
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
