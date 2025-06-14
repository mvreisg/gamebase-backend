<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;

class MariaDBUserRepository implements UserRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(User $user): User
    {
        try {
            $this->pdo->beginTransaction();

            $userName = $user->getUserName();
            $passWord = $user->getPassWord();
            $isActive = (int)$user->getIsActive();

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
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de inserção!'
                );
            }

            $wasInsertExecutionASuccess = $insertStatement->execute([
                ':userName' => $userName,
                ':passWord' => $passWord,
                ':isActive' => $isActive
            ]);

            if ($wasInsertExecutionASuccess === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de inserção!'
                );
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
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasSelectExecutionASuccess = $selectStatement->execute([
                ':id' => $lastInsertedId
            ]);

            if ($wasSelectExecutionASuccess === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $fetchResult = $selectStatement->fetch();

            if ($fetchResult === false) {
                throw new DatabaseFetchFailureException('Ocorreu uma falha ao realizar a busca!');
            }

            $this->pdo->commit();

            $user = new User();
            $user->setId($fetchResult['id']);
            $user->setUserName($fetchResult['username']);
            $user->setPassword($fetchResult['password']);
            $user->setIsActive($fetchResult['is_active']);

            return $user;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException $e
        ) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(User $user): bool
    {
        $id = $user->getId();
        $userName = $user->getUserName();
        $passWord = $user->getPassWord();
        $isActive = (int)$user->getIsActive();

        try {
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
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasStatementExecutionSuccessful = $statement->execute([
                ':userName' => $userName,
                ':passWord' => $passWord,
                ':isActive' => $isActive,
                ':id' => $id
            ]);

            if ($wasStatementExecutionSuccessful === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $numberOfLinesAffected = $statement->rowCount();
            $wasSomeUpdateHappened = $numberOfLinesAffected > 0;
            return $wasSomeUpdateHappened;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $isActive = (int)$isActive;

            $statement = $this->pdo->prepare(
                'UPDATE
                    user
                SET
                    is_active = :isActive
                WHERE
                    id = :id
                AND
                    is_active <> :isActive;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de atualização!'
                );
            }

            $wasTheUpdateSuccessfullyExecuted = $statement->execute([
                ':isActive' => $isActive,
                ':id' => $id
            ]);
            if ($wasTheUpdateSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de atualização!'
                );
            }

            $wasTheUpdateOcurred = $statement->rowCount() > 0;
            return $wasTheUpdateOcurred;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function findByUserName(mixed $userName): User|null
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
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':userName' => $userName
            ]);

            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $result = $statement->fetch();

            if ($result === false) {
                return null;
            }

            $user = new User();
            $user->setId($result['id']);
            $user->setUserName($result['username']);
            $user->setPassword($result['password']);
            $user->setIsActive($result['is_active']);

            return $user;
        } catch (
            DatabaseFetchFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function findById(mixed $id): User|null
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
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':id' => $id
            ]);

            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $result = $statement->fetch();

            if ($result === false) {
                return null;
            }

            $user = new User();
            $user->setId($result['id']);
            $user->setUserName($result['username']);
            $user->setPassword($result['password']);
            $user->setIsActive($result['is_active']);

            return $user;
        } catch (
            DatabaseFetchFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
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
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasTheStatementExecutionSuccessful = $statement->execute();

            if ($wasTheStatementExecutionSuccessful === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $result = $statement->fetchAll();

            if ($result === false) {
                return [];
            }

            $users = [];
            foreach ($result as $row) {
                $user = new User();
                $user->setId($row['id']);
                $user->setUserName($row['username']);
                $user->setPassword($row['password']);
                $user->setIsActive($row['is_active']);
                $users[] = $user;
            }

            return $users;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function hasDuplicatedUserName(string $userName): bool
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
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao tentar criar a declaração de busca!'
                );
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':userName' => $userName
            ]);

            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao tentar executar a declaração de busca!'
                );
            }

            $numberOfLinesAffected = $statement->rowCount();
            $hasDuplicatedNames = $numberOfLinesAffected > 0;

            return $hasDuplicatedNames;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }
}
