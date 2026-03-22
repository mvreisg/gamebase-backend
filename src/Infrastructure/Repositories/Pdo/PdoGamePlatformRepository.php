<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Pdo;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GamePlatformRepositoryInterface;

class PdoGamePlatformRepository implements GamePlatformRepositoryInterface
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function insert(GamePlatform $gamePlatform): GamePlatform
    {
        try {
            $this->connection->beginTransaction();

            $platformId = $gamePlatform->getPlatformIdValue();
            $gameId = $gamePlatform->getGameIdValue();

            $insertStatement = $this->connection->prepare(
                "INSERT INTO game_platform (
                    platform_id, 
                    game_id
                ) 
                VALUES (
                    :platformId, 
                    :gameId
                );"
            );

            $insertStatement->execute([
                ":platformId" => $platformId,
                ":gameId" => $gameId
            ]);

            $lastInsertedId = intval(
                $this->connection->lastInsertId()
            );

            $selectStatement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    game_platform 
                WHERE 
                    id = :id;"
            );

            $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);

            $fetchResult = $selectStatement->fetch();

            $this->connection->commit();

            $return = new GamePlatform(
                Id::make($fetchResult["game_id"]),
                Id::make($fetchResult["platform_id"])
            );
            $return->setId(Id::make($fetchResult["id"]));
            return $return;
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function update(GamePlatform $gamePlatform): bool
    {
        try {
            $id = $gamePlatform->getIdValue();
            $platformId = $gamePlatform->getPlatformIdValue();
            $gameId = $gamePlatform->getGameIdValue();

            $statement = $this->connection->prepare(
                "UPDATE 
                    game_platform 
                SET 
                    platform_id = :platformId,
                    game_id = :gameId
                WHERE 
                    id = :id;"
            );

            $statement->execute([
                ":platformId" => $platformId,
                ":gameId" => $gameId,
                ":id" => $id
            ]);

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

            $statement = $this->connection->prepare(
                "DELETE FROM
                    game_platform
                WHERE
                    id = :id"
            );

            $statement->execute([
                "id" => $idValue,
            ]);

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

            $statement = $this->connection->prepare(
                "SELECT 
                    *
                FROM
                    game_platform
                WHERE
                    id = :id;"
            );

            $statement->execute([
                ":id" => $idValue
            ]);

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                throw new RepositoryUnexistantRegisterException(
                    $idValue
                );
            }

            $return = new GamePlatform(
                Id::make($fetchResult["game_id"]),
                Id::make($fetchResult["platform_id"])
            );
            $return->setId(Id::make($fetchResult["id"]));
            return $return;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): GamePlatformCollection
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    game_platform;"
            );

            $statement->execute();

            $result = $statement->fetchAll();
            if (count($result) === 0) {
                return new GamePlatformCollection();
            }

            $gamePlatforms = new GamePlatformCollection();
            foreach ($result as $row) {
                $value = new GamePlatform(
                    Id::make($row["game_id"]),
                    Id::make($row["platform_id"])
                );
                $value->setId(Id::make($row["id"]));
                $gamePlatforms->add($value);
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

            $statement = $this->connection->prepare(
                "SELECT
                    COUNT(*) 
                    AS
                    $alias
                FROM
                    game_platform
                WHERE
                    id = :id;"
            );

            $statement->execute([
                ":id" => $idValue
            ]);

            $fetchResult = $statement->fetch();
            $numberOfIds = intval(
                $fetchResult[
                    $alias
                ]
            );

            if ($numberOfIds === 0) {
                throw new RepositoryUnexistantRegisterException(
                    $idValue
                );
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
