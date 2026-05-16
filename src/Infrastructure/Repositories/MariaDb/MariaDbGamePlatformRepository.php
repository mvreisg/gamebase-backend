<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb;

use Mvreisg\GamebaseBackend\Domain\Game\Entity\Game;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Entity\Collection\GamePlatformCollection;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Entity\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Repository\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Platform;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class MariaDbGamePlatformRepository implements GamePlatformRepositoryInterface
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

            $platformId = $gamePlatform->getPlatform()->getId()->getValue();
            $gameId = $gamePlatform->getGame()->getId()->getValue();

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
                    gmpl.id AS gmpl_id,
                    gmpl.game_id AS gmpl_game_id,
                    gmpl.platform_id AS gmpl_platform_id,
                    gm.id AS gm_id,
                    gm.name AS gm_name,
                    gm.is_active AS gm_is_active,
                    pl.id AS pl_id,
                    pl.name AS pl_name,
                    pl.is_active AS pl_is_active
                FROM 
                    game_platform gmpl
                JOIN
                    game gm
                ON
                    gmpl.game_id = gm.id
                JOIN
                    platform pl
                ON
                    gmpl.platform_id = pl.id
                WHERE 
                    gmpl.id = :id;"
            );

            $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);

            $fetchResult = $selectStatement->fetch();

            $this->connection->commit();

            $value = GamePlatform::create(
                Id::create(
                    $fetchResult["gmpl_id"]
                ),
                Game::create(
                    Id::create(
                        $fetchResult["gm_id"]
                    ),
                    Name::create(
                        $fetchResult["gm_name"]
                    ),
                    $fetchResult["gm_is_active"]
                ),
                Platform::create(
                    Id::create(
                        $fetchResult["pl_id"]
                    ),
                    Name::create(
                        $fetchResult["pl_name"]
                    ),
                    $fetchResult["pl_is_active"]
                ),
            );
            return $value;
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function update(GamePlatform $gamePlatform): bool
    {
        try {
            $id = $gamePlatform->getId()->getValue();
            $platformId = $gamePlatform->getPlatform()->getId()->getValue();
            $gameId = $gamePlatform->getGame()->getId()->getValue();

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

    public function findById(Id $id): ?GamePlatform
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->connection->prepare(
                "SELECT 
                    gmpl.id AS gmpl_id,
                    gmpl.game_id AS gmpl_game_id,
                    gmpl.platform_id AS gmpl_platform_id,
                    gm.id AS gm_id,
                    gm.name AS gm_name,
                    gm.is_active AS gm_is_active,
                    pl.id AS pl_id,
                    pl.name AS pl_name,
                    pl.is_active AS pl_is_active
                FROM 
                    game_platform gmpl
                JOIN
                    game gm
                ON
                    gmpl.game_id = gm.id
                JOIN
                    platform pl
                ON
                    gmpl.platform_id = pl.id
                WHERE 
                    gmpl.id = :id;"
            );

            $statement->execute([
                ":id" => $idValue
            ]);

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                return null;
            }

            $value = GamePlatform::create(
                Id::create(
                    $fetchResult["gmpl_id"]
                ),
                Game::create(
                    Id::create(
                        $fetchResult["gm_id"]
                    ),
                    Name::create(
                        $fetchResult["gm_name"]
                    ),
                    $fetchResult["gm_is_active"]
                ),
                Platform::create(
                    Id::create(
                        $fetchResult["pl_id"]
                    ),
                    Name::create(
                        $fetchResult["pl_name"]
                    ),
                    $fetchResult["pl_is_active"]
                ),
            );
            return $value;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): ?GamePlatformCollection
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT 
                    gmpl.id AS gmpl_id,
                    gmpl.game_id AS gmpl_game_id,
                    gmpl.platform_id AS gmpl_platform_id,
                    gm.id AS gm_id,
                    gm.name AS gm_name,
                    gm.is_active AS gm_is_active,
                    pl.id AS pl_id,
                    pl.name AS pl_name,
                    pl.is_active AS pl_is_active
                FROM 
                    game_platform gmpl
                JOIN
                    game gm
                ON
                    gmpl.game_id = gm.id
                JOIN
                    platform pl
                ON
                    gmpl.platform_id = pl.id;"
            );

            $statement->execute();

            $result = $statement->fetchAll();
            if (count($result) === 0) {
                return null;
            }

            $gamePlatforms = new GamePlatformCollection();
            foreach ($result as $row) {
                $value = GamePlatform::create(
                    Id::create(
                        $row["gmpl_id"]
                    ),
                    Game::create(
                        Id::create(
                            $row["gm_id"]
                        ),
                        Name::create(
                            $row["gm_name"]
                        ),
                        $row["gm_is_active"]
                    ),
                    Platform::create(
                        Id::create(
                            $row["pl_id"]
                        ),
                        Name::create(
                            $row["pl_name"]
                        ),
                        $row["pl_is_active"]
                    ),
                );
                $gamePlatforms->add($value);
            }
            return $gamePlatforms;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkIfExists(Id $id): bool
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

            return $numberOfIds > 0;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
