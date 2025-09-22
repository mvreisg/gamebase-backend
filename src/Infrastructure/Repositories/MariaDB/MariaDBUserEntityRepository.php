<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use PDO;
use Mvreisg\GamebaseBackend\Domain\Entities\UserEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBUnexistantRegisterException;

class MariaDBUserEntityRepository implements UserEntityRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(UserEntity $userEntity): UserEntity
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBTransactionCreationFailureException();
            }

            $userName = $userEntity->getUserName();
            $passWord = $userEntity->getPassWord();
            $isActive = intval(
                $userEntity->getIsActive()
            );

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    user (
                        username, 
                        password,
                        is_active
                    ) 
                VALUES (
                    :userName, 
                    :passWord, 
                    :isActive
                );'
            );

            if ($insertStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasInsertExecutionASuccess = $insertStatement->execute([
                ':userName' => $userName,
                ':passWord' => $passWord,
                ':isActive' => $isActive
            ]);

            if ($wasInsertExecutionASuccess === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $lastInsertedId = $this->pdo->lastInsertId();
            $lastInsertedId = intval($lastInsertedId);

            $selectStatement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    user 
                WHERE 
                    id = :id;'
            );

            if ($selectStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasSelectExecutionASuccess = $selectStatement->execute([
                ':id' => $lastInsertedId
            ]);

            if ($wasSelectExecutionASuccess === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $selectStatement->fetch();

            if ($fetchResult === false) {
                throw new MariaDBFetchFailureException();
            }

            $this->pdo->commit();

            $userEntity = new UserEntity(
                $fetchResult['id'],
                $fetchResult['username'],
                $fetchResult['password'],
                boolval(
                    $fetchResult['is_active']
                )
            );

            return $userEntity;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(UserEntity $userEntity): bool
    {
        try {
            $id = $userEntity->getId();
            $userName = $userEntity->getUserName();
            $passWord = $userEntity->getPassWord();
            $isActive = intval(
                $userEntity->getIsActive()
            );

            $statement = $this->pdo->prepare(
                'UPDATE 
                    user 
                SET 
                    username = :userName, 
                    password = :passWord, 
                    is_active = :isActive 
                WHERE 
                    id = :id;'
            );

            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasStatementExecutionSuccessful = $statement->execute([
                ':userName' => $userName,
                ':passWord' => $passWord,
                ':isActive' => $isActive,
                ':id' => $id
            ]);

            if ($wasStatementExecutionSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $numberOfLinesAffected = $statement->rowCount();
            $wasSomeUpdateHappened = $numberOfLinesAffected > 0;
            if ($wasSomeUpdateHappened == false) {
                throw new MariaDBUnexistantRegisterException(
                    $id
                );
            }
            return $wasSomeUpdateHappened;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $isActive = intval($isActive);

            $query = $this->pdo->prepare(
                'SELECT * FROM user WHERE id = :id'
            );
            if ($query === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheQuerySuccessfullyExecuted = $query->execute([
                ':id' => $id
            ]);
            if ($wasTheQuerySuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $registerExists = $query->rowCount() > 0;
            if ($registerExists === false) {
                throw new MariaDBUnexistantRegisterException(
                    $id
                );
            }

            $result = $query->fetch();
            $fetchedIsActive = intval(
                $result['is_active']
            );
            if ($fetchedIsActive === $isActive) {
                return false;
            }

            $statement = $this->pdo->prepare(
                'UPDATE
                    user
                SET
                    is_active = :isActive
                WHERE
                    id = :id;'
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheUpdateSuccessfullyExecuted = $statement->execute([
                ':isActive' => $isActive,
                ':id' => $id
            ]);
            if ($wasTheUpdateSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $registerHasBeenChanged = $statement->rowCount() > 0;
            return $registerHasBeenChanged;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): UserEntity
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    user 
                WHERE 
                    id = :id;'
            );

            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':id' => $id
            ]);

            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBUnexistantRegisterException(
                    'O registro com o id ' . $id . ' não existe!'
                );
            }

            $userEntity = new UserEntity(
                $fetchResult['id'],
                $fetchResult['username'],
                $fetchResult['password'],
                boolval(
                    $fetchResult['is_active']
                )
            );

            return $userEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findByUserName(string $userName): UserEntity
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    user 
                WHERE 
                    username = :userName;'
            );

            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':userName' => $userName
            ]);

            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();

            if ($fetchResult === false) {
                throw new MariaDBUnexistantRegisterException(
                    $userName
                );
            }

            $userEntity = new UserEntity(
                $fetchResult['id'],
                $fetchResult['username'],
                $fetchResult['password'],
                boolval(
                    $fetchResult['is_active']
                )
            );

            return $userEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    user;'
            );

            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementExecutionSuccessful = $statement->execute();

            if ($wasTheStatementExecutionSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetchAll();

            if ($fetchResult === false) {
                return [];
            }

            $userEntities = [];
            foreach ($fetchResult as $row) {
                $userEntity = new UserEntity(
                    $row['id'],
                    $row['username'],
                    $row['password'],
                    boolval(
                        $row['is_active']
                    )
                );

                $userEntities[] = $userEntity;
            }

            return $userEntities;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkDuplicatedUserNames(string $userName): void
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    user 
                WHERE 
                    username = :userName;'
            );

            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':userName' => $userName
            ]);

            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $numberOfLinesAffected = $statement->rowCount();
            if ($numberOfLinesAffected > 0) {
                throw new MariaDBDuplicatedEntryException(
                    $userName
                );
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
