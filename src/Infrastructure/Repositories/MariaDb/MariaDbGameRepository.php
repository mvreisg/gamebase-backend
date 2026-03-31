<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb;

use GameCollection;
use Mvreisg\GamebaseBackend\Domain\Game\Entity\Game;
use Mvreisg\GamebaseBackend\Domain\Game\Repository\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class MariaDbGameRepository implements GameRepositoryInterface
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function insert(Game $game): Game
    {
        try {
            $this->connection->beginTransaction();

            $name = $game->getName()->getValue();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $game->getIsActive()
            );

            $insertStatement = $this->connection->prepare(
                "INSERT INTO 
                    game (
                        name, 
                        is_active
                    ) 
                VALUES (
                    :name, 
                    :isActive
                );"
            );

            $insertStatement->execute([
                ":name" => $name,
                ":isActive" => $isActive
            ]);

            $lastInsertedId = intval(
                $this->connection->lastInsertId()
            );

            $selectStatement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    game 
                WHERE 
                    id = :id;"
            );

            $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);

            $fetchResult = $selectStatement->fetch();

            $this->connection->commit();

            $return = new Game(
                Name::make($fetchResult["name"]),
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult["is_active"]
                ),
            );
            $return->setId(Id::make($fetchResult["id"]));
            return $return;
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function update(Game $game): bool
    {
        try {
            $id = $game->getId()->getValue();
            $name = $game->getName()->getValue();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $game->getIsActive()
            );

            $statement = $this->connection->prepare(
                "UPDATE 
                    game 
                SET 
                    name = :name, 
                    is_active = :isActive 
                WHERE 
                    id = :id;"
            );

            $statement->execute([
                ":name" => $name,
                ":id" => $id,
                ":isActive" => $isActive
            ]);

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        try {
            $idValue = $id->getValue();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $intIsActive = intval($isActive);

            $statement = $this->connection->prepare(
                "UPDATE
                    game
                SET
                    is_active = :isActive
                WHERE
                    id = :id
                AND
                    is_active <> :isActive;"
            );

            $statement->execute([
                ":isActive" => $intIsActive,
                ":id" => $idValue
            ]);

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): ?Game
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    game 
                WHERE 
                    id = :id;"
            );

            $statement->execute([
                ":id" => $idValue
            ]);

            $fetchResult = $statement->fetch();

            if ($fetchResult === false) {
                return null;
            }

            $return = new Game(
                Name::make($fetchResult["name"]),
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult["is_active"]
                ),
            );
            $return->setId(Id::make($fetchResult["id"]));
            return $return;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): GameCollection
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    game;"
            );

            $statement->execute();

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) === 0) {
                return new GameCollection();
            }

            $games = new GameCollection();
            foreach ($fetchResult as $row) {
                $value = new Game(
                    Name::make($row["name"]),
                    /* MariaDB stores bool as int values so a casting
                    * here is needed.
                    */
                    boolval(
                        $row["is_active"]
                    ),
                );
                $value->setId(Id::make($row["id"]));
                $games->add($value);
            }
            return $games;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkIfExists(Id $id): bool
    {
        try {
            $idValue = $id->getValue();

            $alias = "number_of_ids";
            $statement = $this->connection->prepare(
                "SELECT
                    COUNT(*) 
                    AS
                    $alias
                FROM
                    game
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

    public function checkDuplicatedNames(Name $name): bool
    {
        try {
            $nameValue = $name->getValue();

            $alias = "number_of_names";
            $statement = $this->connection->prepare(
                "SELECT 
                    COUNT(*)
                    AS
                    $alias
                FROM 
                    game 
                WHERE 
                    name = :name;"
            );

            $statement->execute([
                ":name" => $nameValue
            ]);

            $fetchResult = $statement->fetch();
            $numberOfNames = intval(
                $fetchResult[
                    $alias
                ]
            );

            return $numberOfNames > 0;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
