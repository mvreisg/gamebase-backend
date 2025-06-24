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
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseUnexistantRegisterException;
use Throwable;

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
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new DatabaseTransactionCreationFailureException(
                    'Ocorreu um erro ao criar a transação!'
                );
            }

            $userName = $user->getUserName();
            $passWord = $user->getPassWord();
            $isActive = intval(
                $user->getIsActive()
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
            $user->setIsActive(
                boolval($fetchResult['is_active'])
            );

            return $user;
        } catch (
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException |
            Throwable $e
        ) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(User $user): bool
    {
        try {
            $id = $user->getId();
            $userName = $user->getUserName();
            $passWord = $user->getPassWord();
            $isActive = intval(
                $user->getIsActive()
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
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de busca!'
                );
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
            if ($wasSomeUpdateHappened == false){
                throw new DatabaseUnexistantRegisterException(
                    'O registro com o id ' . $id . ' não existe!'
                );
            }
            return $wasSomeUpdateHappened;
        } catch (
            DatabaseUnexistantRegisterException | 
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException |
            Throwable $e
        ) {
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
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de atualização!'
                );
            }

            $wasTheQuerySuccessfullyExecuted = $query->execute([
                ':id' => $id
            ]);
            if ($wasTheQuerySuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de atualização!'
                );
            }
            
            $registerExists = $query->rowCount() > 0;
            if ($registerExists === false){
                throw new DatabaseUnexistantRegisterException(
                    'O registro com o id ' . $id . ' não existe!'
                );
            }

            $result = $query->fetch();
            $fetchedIsActive = intval(
                $result['is_active']
            );
            if ($fetchedIsActive === $isActive){
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

            $registerHasBeenChanged = $statement->rowCount() > 0;
            return $registerHasBeenChanged;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException |
            Throwable $e
        ) {
            throw $e;
        }
    }

    public function findById(int $id): User
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
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de busca!'
                );
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':id' => $id
            ]);

            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                throw new DatabaseUnexistantRegisterException(
                    'O registro com o id ' . $id . ' não existe!'
                );
            }

            $user = new User();
            $user->setId($fetchResult['id']);
            $user->setUserName($fetchResult['username']);
            $user->setPassword($fetchResult['password']);
            $user->setIsActive(
                boolval($fetchResult['is_active'])
            );

            return $user;
        } catch (
            DatabaseUnexistantRegisterException | 
            DatabaseFetchFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException |
            Throwable $e
        ) {
            throw $e;
        }
    }

    public function findByUserName(string $userName): User
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

            $fetchResult = $statement->fetch();

            if ($fetchResult === false) {
                throw new DatabaseUnexistantRegisterException(
                    'O registro com o username ' . $userName . ' não existe!'
                );
            }

            $user = new User();
            $user->setId($fetchResult['id']);
            $user->setUserName($fetchResult['username']);
            $user->setPassword($fetchResult['password']);
            $user->setIsActive(
                boolval($fetchResult['is_active'])
            );

            return $user;
        } catch (
            DatabaseUnexistantRegisterException | 
            DatabaseFetchFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException |
            Throwable $e
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

            $fetchResult = $statement->fetchAll();

            if ($fetchResult === false) {
                return [];
            }

            $users = [];
            foreach ($fetchResult as $row) {
                $user = new User();
                $user->setId($row['id']);
                $user->setUserName($row['username']);
                $user->setPassword($row['password']);
                $user->setIsActive(
                    boolval($row['is_active'])
                );
                $users[] = $user;
            }

            return $users;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException |
            Throwable $e
        ) {
            throw $e;
        }
    }

    public function hasDuplicatedUserNames(string $userName): bool
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
