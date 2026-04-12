<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb;

use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Collection\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Permission\Repository\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class MariaDbPermissionRepository implements PermissionRepositoryInterface
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function insert(Permission $permission): Permission
    {
        try {
            $this->connection->beginTransaction();

            $name = $permission->getName()->getValue();
            $value = $permission->getPermissionValue()->getValue()->value;

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $permission->getIsActive()
            );

            $insertStatement = $this->connection->prepare(
                "INSERT INTO 
                    permission 
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
                    permission
                WHERE
                    id = :id;"
            );

            $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);

            $fetchResult = $selectStatement->fetch();

            $this->connection->commit();

            $return = new Permission(
                Id::create($fetchResult["id"]),
                Name::create($fetchResult["name"]),
                PermissionValue::create($fetchResult["value"]),
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult["is_active"]
                )
            );
            return $return;
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function update(Permission $permission): bool
    {
        try {
            $id = $permission->getId()->getValue();
            $name = $permission->getName()->getValue();
            $value = $permission->getPermissionValue()->getValue()->value;

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $permission->getIsActive()
            );

            $statement = $this->connection->prepare(
                "UPDATE
                    permission
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
                ":id" => $id,
                ":value" => $value
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
                    permission
                SET
                    is_active = :isActive
                WHERE
                    id = :id;"
            );

            $statement->execute([
                ":id" => $idValue,
                ":isActive" => $intIsActive
            ]);

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): ?Permission
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM
                    permission
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

            $return = new Permission(
                Id::create($fetchResult["id"]),
                Name::create($fetchResult["name"]),
                PermissionValue::create($fetchResult["value"]),
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult["is_active"]
                )
            );
            return $return;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): ?PermissionCollection
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT 
                    *
                FROM
                    permission;"
            );

            $statement->execute();

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) === 0) {
                return null;
            }

            $permissions = new PermissionCollection();
            foreach ($fetchResult as $row) {
                $value = new Permission(
                    Id::create($row["id"]),
                    Name::create($row["name"]),
                    PermissionValue::create($row["value"]),
                    /* MariaDB stores bool as int values so a casting
                    * here is needed.
                    */
                    boolval(
                        $row["is_active"]
                    )
                );
                $permissions->add($value);
            }

            return $permissions;
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
                    permission
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

    public function checkDuplicatedNames(?Id $id = null, Name $name): bool
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
                        permission 
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
                        permission 
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

    public function checkDuplicatedValues(?Id $id = null, PermissionValue $value): bool
    {
        try {
            $idValue = $id ? $id->getValue() : null;
            $valueValue = $value->getValue()->value;

            $alias = "number_of_values";
            if ($idValue === null) {
                $statement = $this->connection->prepare(
                    "SELECT 
                        COUNT(*)
                        AS
                        $alias
                    FROM 
                        permission 
                    WHERE 
                        value = :value;"
                );
                $statement->execute([
                    ":value" => $valueValue
                ]);
            } else {
                $statement = $this->connection->prepare(
                    "SELECT 
                        COUNT(*)
                        AS
                        $alias
                    FROM 
                        permission 
                    WHERE 
                        value = :value
                    AND
                        id != :id;"
                );
                $statement->execute([
                    ":id" => $idValue,
                    ":value" => $valueValue
                ]);
            }

            $fetchResult = $statement->fetch();
            $numberOfValues = intval(
                $fetchResult[
                    $alias
                ]
            );
            return $numberOfValues > 0;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
