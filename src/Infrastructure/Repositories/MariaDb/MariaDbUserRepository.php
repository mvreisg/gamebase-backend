<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb;

use Mvreisg\GamebaseBackend\Domain\Entities\EncodedPassword;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Entities\UserCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;

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
                Username::make($fetchResult["username"]),
                EncodedPassword::make($fetchResult["password"]),
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

    public function findById(Id $id): User
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
                throw new RepositoryUnexistantRegisterException(
                    $idValue
                );
            }

            $return = new User(
                Username::make($fetchResult["username"]),
                EncodedPassword::make($fetchResult["password"]),
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

    public function findByUsername(Username $username): User
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
                throw new RepositoryUnexistantRegisterException(
                    $usernameValue
                );
            }

            $return = new User(
                Username::make($fetchResult["username"]),
                EncodedPassword::make($fetchResult["password"]),
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

    public function findAll(): UserCollection
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
                return new UserCollection();
            }

            $users = new UserCollection();
            foreach ($fetchResult as $row) {
                $user = new User(
                    Username::make($row["username"]),
                    EncodedPassword::make($row["password"]),
                    /* MariaDB stores bool as int values so a casting
                    * here is needed.
                    */
                    boolval(
                        $row["is_active"]
                    )
                );
                $user->setId(Id::make($row["id"]));
                $users->add(
                    $user
                );
            }
            return $users;
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

            if ($numberOfIds === 0) {
                throw new RepositoryUnexistantRegisterException(
                    $idValue
                );
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkDuplicatedUsernames(Username $username): void
    {
        try {
            $usernameValue = $username->getValue();
            $alias = "number_of_names";

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

            $fetchResult = $statement->fetch();
            $numberOfNames = intval(
                $fetchResult[
                    $alias
                ]
            );
            if ($numberOfNames > 0) {
                throw new RepositoryDuplicatedRegisterException(
                    $usernameValue
                );
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
