<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Entity\Collection\UserCollection;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Encoded\EncodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

class MariaDbUserRepository implements UserRepositoryInterface
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function insert(User $user): User
    {
        try {
            $this->connection->beginTransaction();

            $username = $user->getUsername()->getValue();
            $password = $user->getPassword()->getValue();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $user->getIsActive()
            );

            $insertStatement = $this->connection->prepare(
                "INSERT INTO 
                    user (
                        username, 
                        password,
                        is_active
                    ) 
                VALUES (
                    :username, 
                    :password, 
                    :isActive
                );"
            );

            $insertStatement->execute([
                ":username" => $username,
                ":password" => $password,
                ":isActive" => $isActive
            ]);

            $lastInsertedId = intval(
                $this->connection->lastInsertId()
            );

            $selectStatement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    user 
                WHERE 
                    id = :id;"
            );

            $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);

            $fetchResult = $selectStatement->fetch();

            $this->connection->commit();

            $return = new User(
                Id::create($fetchResult["id"]),
                Username::create($fetchResult["username"]),
                EncodedPassword::create($fetchResult["password"]),
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

    public function update(User $user): bool
    {
        try {
            $id = $user->getId()->getValue();
            $username = $user->getUsername()->getValue();
            $password = $user->getPassword()->getValue();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $user->getIsActive()
            );

            $statement = $this->connection->prepare(
                "UPDATE 
                    user 
                SET 
                    username = :username, 
                    password = :password, 
                    is_active = :isActive 
                WHERE 
                    id = :id;"
            );

            $statement->execute([
                ":username" => $username,
                ":password" => $password,
                ":isActive" => $isActive,
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
            $intIsActive = intval(
                $isActive
            );

            $statement = $this->connection->prepare(
                "UPDATE
                    user
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

    public function findById(Id $id): ?User
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    user 
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

            $return = new User(
                Id::create($fetchResult["id"]),
                Username::create($fetchResult["username"]),
                EncodedPassword::create($fetchResult["password"]),
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

    public function findByUsername(Username $username): ?User
    {
        try {
            $usernameValue = $username->getValue();

            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    user 
                WHERE 
                    username = :username;"
            );

            $statement->execute([
                ":username" => $usernameValue
            ]);

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                return null;
            }

            $return = new User(
                Id::create($fetchResult["id"]),
                Username::create($fetchResult["username"]),
                EncodedPassword::create($fetchResult["password"]),
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

    public function findAll(): ?UserCollection
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    user;"
            );

            $statement->execute();

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) === 0) {
                return null;
            }

            $users = new UserCollection();
            foreach ($fetchResult as $row) {
                $user = new User(
                    Id::create($row["id"]),
                    Username::create($row["username"]),
                    EncodedPassword::create($row["password"]),
                    /* MariaDB stores bool as int values so a casting
                    * here is needed.
                    */
                    boolval(
                        $row["is_active"]
                    )
                );
                $users->add(
                    $user
                );
            }
            return $users;
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
                    user
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

    public function checkDuplicatedUsernames(?Id $id, Username $username): bool
    {
        try {
            $idValue = $id ? $id->getValue() : null;
            $usernameValue = $username->getValue();
            $alias = "number_of_names";

            $statement = null;
            if ($idValue === null) {
                $statement = $this->connection->prepare(
                    "SELECT 
                        COUNT(*)
                        AS
                        $alias
                    FROM 
                        user 
                    WHERE 
                        username = :username;"
                );
                $statement->execute([
                    ":username" => $usernameValue
                ]);
            } else {
                $statement = $this->connection->prepare(
                    "SELECT 
                        COUNT(*)
                        AS
                        $alias
                    FROM 
                        user 
                    WHERE 
                        username = :username
                    AND
                        id <> :id;"
                );
                $statement->execute([
                    ":username" => $usernameValue,
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
