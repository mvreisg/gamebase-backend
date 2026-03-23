<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Pdo;

use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Entities\Sector;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;

class PdoSectorRepository implements SectorRepositoryInterface
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function insert(Sector $sector): Sector
    {
        try {
            $this->connection->beginTransaction();

            $name = $sector->getNameValue();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $sector->getIsActive()
            );

            $value = $sector->getSectorValue();

            $insertStatement = $this->connection->prepare(
                "INSERT INTO 
                    sector 
                (
                    name,
                    is_active,
                    value
                )
                VALUES (
                    :name,
                    :isActive,
                    :value
                );"
            );

            $insertStatement->execute([
                ":name" => $name,
                ":isActive" => $isActive,
                ":value" => $value
            ]);

            $lastInsertedId = intval(
                $this->connection->lastInsertId()
            );

            $selectStatement = $this->connection->prepare(
                "SELECT 
                    *
                FROM
                    sector
                WHERE
                    id = :id;"
            );

            $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);

            $fetchResult = $selectStatement->fetch();

            $this->connection->commit();

            $return = new Sector(
                Name::make($fetchResult["name"]),
                SectorValue::make($fetchResult["value"]),
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult["is_active"]
                )
            );
            $return->setId(Id::make($fetchResult["id"]));
            return $return;
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function update(Sector $sector): bool
    {
        try {
            $id = $sector->getIdValue();
            $name = $sector->getNameValue();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $sector->getIsActive()
            );

            $value = $sector->getSectorValue();

            $statement = $this->connection->prepare(
                "UPDATE
                    sector
                SET
                    name = :name,
                    is_active = :isActive,
                    value = :value
                WHERE
                    id = :id;"
            );

            $statement->execute([
                ":name" => $name,
                ":isActive" => $isActive,
                ":value" => $value,
                ":id" => $id
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
                    sector
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

    public function findById(Id $id): Sector
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM
                    sector
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

            $return = new Sector(
                Name::make($fetchResult["name"]),
                SectorValue::make($fetchResult["value"]),
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult["is_active"]
                )
            );
            $return->setId(Id::make($fetchResult["id"]));
            return $return;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): SectorCollection
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT 
                    *
                FROM
                    sector;"
            );

            $statement->execute();

            $fetchResult = $statement->fetchAll();

            if ($fetchResult === false) {
                return new SectorCollection(null);
            }

            $sectors = new SectorCollection(null);
            foreach ($fetchResult as $row) {
                $value = new Sector(
                    Name::make($row["name"]),
                    SectorValue::make($row["value"]),
                    /* MariaDB stores bool as int values so a casting
                    * here is needed.
                    */
                    boolval(
                        $row["is_active"]
                    )
                );
                $value->setId(Id::make($row["id"]));
                $sectors->add($value);
            }
            return $sectors;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkIfExists(Id $id): void
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
                    sector
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

    public function checkDuplicatedNames(Name $name): void
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
                    sector 
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
            if ($numberOfNames > 0) {
                throw new RepositoryDuplicatedRegisterException(
                    $nameValue
                );
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
