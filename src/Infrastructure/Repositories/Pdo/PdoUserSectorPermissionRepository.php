<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Pdo;

use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;

class PdoUserSectorPermissionRepository implements UserSectorPermissionRepositoryInterface
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function insert(UserSectorPermission $userSectorPermission): UserSectorPermission
    {
        try {
            $this->connection->beginTransaction();

            $userId = $userSectorPermission->getUserIdValue();
            $sectorId = $userSectorPermission->getSectorIdValue();
            $permissionId = $userSectorPermission->getPermissionIdValue();

            $insertStatement = $this->connection->prepare(
                "INSERT INTO user_sector_permission (
                    user_id, 
                    sector_id,
                    permission_id
                ) 
                VALUES (
                    :userId, 
                    :sectorId,
                    :permissionId
                );"
            );

            $insertStatement->execute([
                ":userId" => $userId,
                ":sectorId" => $sectorId,
                ":permissionId" => $permissionId
            ]);

            $lastInsertedId = intval(
                $this->connection->lastInsertId()
            );

            $selectStatement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    user_sector_permission 
                WHERE 
                    id = :id;"
            );

            $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);

            $fetchResult = $selectStatement->fetch();

            $this->connection->commit();

            $return = new UserSectorPermission(
                Id::make($fetchResult["user_id"]),
                Id::make($fetchResult["sector_id"]),
                Id::make($fetchResult["permission_id"])
            );
            $return->setId(Id::make($fetchResult["id"]));
            return $return;
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function update(UserSectorPermission $userSectorPermission): bool
    {
        try {
            $id = $userSectorPermission->getIdValue();
            $userId = $userSectorPermission->getUserIdValue();
            $sectorId = $userSectorPermission->getSectorIdValue();
            $permissionId = $userSectorPermission->getPermissionIdValue();

            $statement = $this->connection->prepare(
                "UPDATE 
                    user_sector_permission 
                SET 
                    user_id = :userId, 
                    sector_id = :sectorId,
                    permission_id = :permissionId 
                WHERE 
                    id = :id;"
            );

            $statement->execute([
                ":id" => $id,
                ":userId" => $userId,
                ":sectorId" => $sectorId,
                ":permissionId" => $permissionId
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
                    user_sector_permission
                WHERE
                    id = :id;"
            );

            $statement->execute([
                ":id" => $idValue
            ]);

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): UserSectorPermission
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    user_sector_permission 
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

            $return = new UserSectorPermission(
                Id::make($fetchResult["user_id"]),
                Id::make($fetchResult["sector_id"]),
                Id::make($fetchResult["permission_id"])
            );
            $return->setId(Id::make($fetchResult["id"]));
            return $return;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAllByUserId(Id $userId): UserSectorPermissionCollection
    {
        try {
            $userIdValue = $userId->getValue();

            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    user_sector_permission
                WHERE
                    user_id = :userId;"
            );

            $statement->execute([
                ":userId" => $userIdValue
            ]);

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) === 0) {
                return new UserSectorPermissionCollection(null);
            }

            $userSectorPermissions = new UserSectorPermissionCollection(null);
            foreach ($fetchResult as $row) {
                $value = new UserSectorPermission(
                    Id::make($row["user_id"]),
                    Id::make($row["sector_id"]),
                    Id::make($row["permission_id"])
                );
                $value->setId(Id::make($row["id"]));
                $userSectorPermissions->add($value);
            }
            return $userSectorPermissions;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): UserSectorPermissionCollection
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    user_sector_permission;"
            );

            $statement->execute();

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) === 0) {
                return new UserSectorPermissionCollection(null);
            }

            $userSectorPermissions = new UserSectorPermissionCollection(null);
            foreach ($fetchResult as $row) {
                $value = new UserSectorPermission(
                    Id::make($row["user_id"]),
                    Id::make($row["sector_id"]),
                    Id::make($row["permission_id"])
                );
                $value->setId(Id::make($row["id"]));
                $userSectorPermissions->add($value);
            }
            return $userSectorPermissions;
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
                    user_sector_permission
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
