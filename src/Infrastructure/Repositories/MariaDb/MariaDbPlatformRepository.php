<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb;

use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Collection\PlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Platform;
use Mvreisg\GamebaseBackend\Domain\Platform\Repository\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class MariaDbPlatformRepository implements PlatformRepositoryInterface
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function insert(Platform $platform): Platform
    {
        try {
            $this->connection->beginTransaction();

            $name = $platform->getName()->getValue();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $platform->getIsActive()
            );

            $insertStatement = $this->connection->prepare(
                "INSERT INTO 
                    platform (
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
                    platform 
                WHERE 
                    id = :id;"
            );

            $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);

            $fetchResult = $selectStatement->fetch();

            $this->connection->commit();

            $value = Platform::create(
                Id::create(
                    $fetchResult["id"]
                ),
                Name::create(
                    $fetchResult["name"]
                ),
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult["is_active"]
                )
            );
            return $value;
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function update(Platform $platform): bool
    {
        try {
            $id = $platform->getId()->getValue();
            $name = $platform->getName()->getValue();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $platform->getIsActive()
            );

            $statement = $this->connection->prepare(
                "UPDATE 
                    platform 
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
            $isActive = intval($isActive);

            $statement = $this->connection->prepare(
                "UPDATE
                    platform
                SET
                    is_active = :isActive
                WHERE
                    id = :id
                AND
                    is_active <> :isActive;"
            );

            $statement->execute([
                ":id" => $idValue,
                ":isActive" => $isActive
            ]);

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): ?Platform
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    platform 
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

            $value = Platform::create(
                Id::create(
                    $fetchResult["id"]
                ),
                Name::create(
                    $fetchResult["name"]
                ),
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult["is_active"]
                )
            );
            return $value;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): ?PlatformCollection
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    platform;"
            );

            $statement->execute();

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) === 0) {
                return null;
            }

            $platforms = new PlatformCollection();
            foreach ($fetchResult as $row) {
                $value = Platform::create(
                    Id::create(
                        $row["id"]
                    ),
                    Name::create(
                        $row["name"]
                    ),
                    /* MariaDB stores bool as int values so a casting
                    * here is needed.
                    */
                    boolval(
                        $row["is_active"]
                    )
                );
                $platforms->add($value);
            }
            return $platforms;
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
                    platform
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

    public function checkDuplicatedNames(?Id $id, Name $name): bool
    {
        try {
            $idValue = $id ? $id->getValue() : null;
            $nameValue = $name->getValue();

            $alias = "number_of_names";
            if ($idValue === null) {
                $statement = $this->connection->prepare(
                    "SELECT 
                        COUNT(*)
                        AS
                        $alias
                    FROM 
                        platform 
                    WHERE 
                        name = :name;"
                );

                $statement->execute([
                    ":name" => $nameValue
                ]);
            } else {
                $statement = $this->connection->prepare(
                    "SELECT 
                        COUNT(*)
                        AS
                        $alias
                    FROM 
                        platform 
                    WHERE 
                        name = :name
                    AND
                        id != :id;"
                );

                $statement->execute([
                    ":name" => $nameValue,
                    ":id" => $idValue
                ]);
            }

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
