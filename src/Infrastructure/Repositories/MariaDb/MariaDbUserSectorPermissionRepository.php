<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb;

use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Encoded\EncodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\Collection\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\UserSectorPermissionRepositoryInterface;

class MariaDbUserSectorPermissionRepository implements UserSectorPermissionRepositoryInterface
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

            $userId = $userSectorPermission->getUser()->getId()->getValue();
            $sectorId = $userSectorPermission->getSector()->getId()->getValue();
            $permissionId = $userSectorPermission->getPermission()->getId()->getValue();

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
                    usp.id AS usp_id, 
                    usp.user_id AS usp_user_id,
                    usp.sector_id AS usp_sector_id,
                    usp.permission_id AS usp_permission_id,
                    u.id AS u_id,
                    u.username AS u_username,
                    u.password AS u_password,
                    u.is_active AS u_is_active,
                    s.id AS s_id,
                    s.name AS s_name,
                    s.is_active AS s_is_active,                    
                    s.value AS s_value,
                    p.id AS p_id,
                    p.name AS p_name,
                    p.is_active AS p_is_active,                    
                    p.value AS p_value                    
                FROM 
                    user_sector_permission usp
                JOIN
                    user u
                ON 
                    usp.user_id = u.id
                JOIN
                    sector s
                ON 
                    usp.sector_id = s.id
                JOIN
                    permission p
                ON 
                    usp.permission_id = p.id
                WHERE 
                    usp.id = :id;"
            );

            $selectStatement->execute([
                ":id" => $lastInsertedId,
            ]);

            $fetchResult = $selectStatement->fetch();

            $this->connection->commit();

            $user = User::create(
                Id::create(
                    $fetchResult["u_id"]
                ),
                Username::create(
                    $fetchResult["u_username"]
                ),
                EncodedPassword::create(
                    $fetchResult["u_password"]
                ),
                boolval(
                    $fetchResult["u_is_active"]
                )
            );

            $sector = Sector::create(
                Id::create(
                    $fetchResult["s_id"]
                ),
                Name::create(
                    $fetchResult["s_name"]
                ),
                SectorValue::create(
                    $fetchResult["s_value"]
                ),
                boolval(
                    $fetchResult["s_is_active"]
                )
            );

            $permission = Permission::create(
                Id::create(
                    $fetchResult["p_id"]
                ),
                Name::create(
                    $fetchResult["p_name"]
                ),
                PermissionValue::create(
                    $fetchResult["p_value"]
                ),
                boolval(
                    $fetchResult["p_is_active"]
                )
            );

            $return = new UserSectorPermission(
                $user,
                $sector,
                $permission
            );
            $return->setId(
                Id::create(
                    $fetchResult["usp_id"]
                )
            );
            return $return;
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function update(UserSectorPermission $userSectorPermission): bool
    {
        try {
            $id = $userSectorPermission->getId()->getValue();
            $userId = $userSectorPermission->getUser()->getId()->getValue();
            $sectorId = $userSectorPermission->getSector()->getId()->getValue();
            $permissionId = $userSectorPermission->getPermission()->getId()->getValue();

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

    public function findById(Id $id): ?UserSectorPermission
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->connection->prepare(
                "SELECT 
                    usp.id AS usp_id, 
                    usp.user_id AS usp_user_id,
                    usp.sector_id AS usp_sector_id,
                    usp.permission_id AS usp_permission_id,
                    u.id AS u_id,
                    u.username AS u_username,
                    u.password AS u_password,
                    u.is_active AS u_is_active,
                    s.id AS s_id,
                    s.name AS s_name,
                    s.is_active AS s_is_active,                    
                    s.value AS s_value,
                    p.id AS p_id,
                    p.name AS p_name,
                    p.is_active AS p_is_active,                    
                    p.value AS p_value                    
                FROM 
                    user_sector_permission usp
                JOIN
                    user u
                ON 
                    usp.user_id = u.id
                JOIN
                    sector s
                ON 
                    usp.sector_id = s.id
                JOIN
                    permission p
                ON 
                    usp.permission_id = p.id
                WHERE 
                    usp.id = :id;"
            );

            $statement->execute([
                ":id" => $idValue
            ]);

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                return null;
            }

            $user = User::create(
                Id::create(
                    $fetchResult["u_id"]
                ),
                Username::create(
                    $fetchResult["u_username"]
                ),
                EncodedPassword::create(
                    $fetchResult["u_password"]
                ),
                boolval(
                    $fetchResult["u_is_active"]
                )
            );

            $sector = Sector::create(
                Id::create(
                    $fetchResult["s_id"]
                ),
                Name::create(
                    $fetchResult["s_name"]
                ),
                SectorValue::create(
                    $fetchResult["s_value"]
                ),
                boolval(
                    $fetchResult["s_is_active"]
                )
            );

            $permission = Permission::create(
                Id::create(
                    $fetchResult["p_id"]
                ),
                Name::create(
                    $fetchResult["p_name"]
                ),
                PermissionValue::create(
                    $fetchResult["p_value"]
                ),
                boolval(
                    $fetchResult["p_is_active"]
                )
            );

            $return = new UserSectorPermission(
                $user,
                $sector,
                $permission
            );
            $return->setId(
                Id::create(
                    $fetchResult["usp_id"]
                )
            );
            return $return;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAllByUserId(Id $userId): ?UserSectorPermissionCollection
    {
        try {
            $userIdValue = $userId->getValue();

            $statement = $this->connection->prepare(
                "SELECT 
                    usp.id AS usp_id, 
                    usp.user_id AS usp_user_id,
                    usp.sector_id AS usp_sector_id,
                    usp.permission_id AS usp_permission_id,
                    u.id AS u_id,
                    u.username AS u_username,
                    u.password AS u_password,
                    u.is_active AS u_is_active,
                    s.id AS s_id,
                    s.name AS s_name,
                    s.is_active AS s_is_active,                    
                    s.value AS s_value,
                    p.id AS p_id,
                    p.name AS p_name,
                    p.is_active AS p_is_active,                    
                    p.value AS p_value                    
                FROM 
                    user_sector_permission usp
                JOIN
                    user u
                ON 
                    usp.user_id = u.id
                JOIN
                    sector s
                ON 
                    usp.sector_id = s.id
                JOIN
                    permission p
                ON 
                    usp.permission_id = p.id
                WHERE 
                    usp.user_id = :userId;"
            );

            $statement->execute([
                ":userId" => $userIdValue
            ]);

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) === 0) {
                return null;
            }

            $userSectorPermissions = new UserSectorPermissionCollection();
            foreach ($fetchResult as $row) {
                $user = User::create(
                    Id::create(
                        $row["u_id"]
                    ),
                    Username::create(
                        $row["u_username"]
                    ),
                    EncodedPassword::create(
                        $row["u_password"]
                    ),
                    boolval(
                        $row["u_is_active"]
                    )
                );

                $sector = Sector::create(
                    Id::create(
                        $row["s_id"]
                    ),
                    Name::create(
                        $row["s_name"]
                    ),
                    SectorValue::create(
                        $row["s_value"]
                    ),
                    boolval(
                        $row["s_is_active"]
                    )
                );

                $permission = Permission::create(
                    Id::create(
                        $row["p_id"]
                    ),
                    Name::create(
                        $row["p_name"]
                    ),
                    PermissionValue::create(
                        $row["p_value"]
                    ),
                    boolval(
                        $row["p_is_active"]
                    )
                );

                $value = new UserSectorPermission(
                    $user,
                    $sector,
                    $permission
                );
                $value->setId(
                    Id::create(
                        $row["usp_id"]
                    )
                );
                $userSectorPermissions->add($value);
            }
            return $userSectorPermissions;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): ?UserSectorPermissionCollection
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT 
                    usp.id AS usp_id, 
                    usp.user_id AS usp_user_id,
                    usp.sector_id AS usp_sector_id,
                    usp.permission_id AS usp_permission_id,
                    u.id AS u_id,
                    u.username AS u_username,
                    u.password AS u_password,
                    u.is_active AS u_is_active,
                    s.id AS s_id,
                    s.name AS s_name,
                    s.is_active AS s_is_active,                    
                    s.value AS s_value,
                    p.id AS p_id,
                    p.name AS p_name,
                    p.is_active AS p_is_active,                    
                    p.value AS p_value                    
                FROM 
                    user_sector_permission usp
                JOIN
                    user u
                ON 
                    usp.user_id = u.id
                JOIN
                    sector s
                ON 
                    usp.sector_id = s.id
                JOIN
                    permission p
                ON 
                    usp.permission_id = p.id;"
            );

            $statement->execute();

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) === 0) {
                return null;
            }

            $userSectorPermissions = new UserSectorPermissionCollection();
            foreach ($fetchResult as $row) {
                $user = User::create(
                    Id::create(
                        $row["u_id"]
                    ),
                    Username::create(
                        $row["u_username"]
                    ),
                    EncodedPassword::create(
                        $row["u_password"]
                    ),
                    boolval(
                        $row["u_is_active"]
                    )
                );

                $sector = Sector::create(
                    Id::create(
                        $row["s_id"]
                    ),
                    Name::create(
                        $row["s_name"]
                    ),
                    SectorValue::create(
                        $row["s_value"]
                    ),
                    boolval(
                        $row["s_is_active"]
                    )
                );

                $permission = Permission::create(
                    Id::create(
                        $row["p_id"]
                    ),
                    Name::create(
                        $row["p_name"]
                    ),
                    PermissionValue::create(
                        $row["p_value"]
                    ),
                    boolval(
                        $row["p_is_active"]
                    )
                );

                $value = new UserSectorPermission(
                    $user,
                    $sector,
                    $permission
                );
                $value->setId(
                    Id::create(
                        $row["usp_id"]
                    )
                );
                $userSectorPermissions->add($value);
            }
            return $userSectorPermissions;
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

            return $numberOfIds > 0;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
