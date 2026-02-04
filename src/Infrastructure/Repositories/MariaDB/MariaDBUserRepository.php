<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Domain\Data\EncodedPassword;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\User;
use Mvreisg\GamebaseBackend\Domain\Data\UserCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Username;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryStatementFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions\MariaDBRepositoryUnexistantRegisterException;

class MariaDBUserRepository implements UserRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(User $user): User
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBRepositoryTransactionCreationFailureException();
            }

            $username = $user->getUsernameValue();
            $password = $user->getPasswordValue();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $user->getIsActive()
            );

            $insertStatement = $this->pdo->prepare(
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
            if ($insertStatement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasInsertExecutionASuccess = $insertStatement->execute([
                ":username" => $username,
                ":password" => $password,
                ":isActive" => $isActive
            ]);
            if ($wasInsertExecutionASuccess === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $lastInsertedId = intval(
                $this->pdo->lastInsertId()
            );

            $selectStatement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    user 
                WHERE 
                    id = :id;"
            );
            if ($selectStatement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasSelectExecutionASuccess = $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);
            if ($wasSelectExecutionASuccess === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $selectStatement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBRepositoryStatementFetchFailureException();
            }

            $this->pdo->commit();

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
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(User $user): bool
    {
        try {
            $id = $user->getIdValue();
            $username = $user->getUsernameValue();
            $password = $user->getPasswordValue();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $user->getIsActive()
            );

            $statement = $this->pdo->prepare(
                "UPDATE 
                    user 
                SET 
                    username = :username, 
                    password = :password, 
                    is_active = :isActive 
                WHERE 
                    id = :id;"
            );

            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasStatementExecutionSuccessful = $statement->execute([
                ":username" => $username,
                ":password" => $password,
                ":isActive" => $isActive,
                ":id" => $id
            ]);

            if ($wasStatementExecutionSuccessful === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

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

            $statement = $this->pdo->prepare(
                "UPDATE
                    user
                SET
                    is_active = :isActive
                WHERE
                    id = :id
                AND
                    is_active <> :isActive;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheUpdateSuccessfullyExecuted = $statement->execute([
                ":isActive" => $intIsActive,
                ":id" => $idValue
            ]);
            if ($wasTheUpdateSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

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

            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    user 
                WHERE 
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ":id" => $idValue
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBRepositoryUnexistantRegisterException(
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

            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    user 
                WHERE 
                    username = :username;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ":username" => $usernameValue
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBRepositoryUnexistantRegisterException(
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
            $statement = $this->pdo->prepare(
                "SELECT 
                    * 
                FROM 
                    user;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementExecutionSuccessful = $statement->execute();
            if ($wasTheStatementExecutionSuccessful === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetchAll();
            if ($fetchResult === false) {
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

            $statement = $this->pdo->prepare(
                "SELECT
                    COUNT(*) 
                    AS
                    $alias
                FROM
                    user
                WHERE
                    id = :id;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheCheckSuccessfullyExecuted = $statement->execute([
                ":id" => $idValue
            ]);
            if ($wasTheCheckSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            $numberOfIds = intval(
                $fetchResult[
                    $alias
                ]
            );

            if ($numberOfIds === 0) {
                throw new MariaDBRepositoryUnexistantRegisterException(
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

            $statement = $this->pdo->prepare(
                "SELECT 
                    COUNT(*)
                    AS
                    $alias
                FROM 
                    user 
                WHERE 
                    username = :username;"
            );
            if ($statement === false) {
                throw new MariaDBRepositoryStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ":username" => $usernameValue
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBRepositoryStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            $numberOfNames = intval(
                $fetchResult[
                    $alias
                ]
            );
            if ($numberOfNames > 0) {
                throw new MariaDBRepositoryDuplicatedRegisterException(
                    $usernameValue
                );
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
