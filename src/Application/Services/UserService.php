<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;

class UserService
{
    private UserRepositoryInterface $repository;
    private EncryptionInterface $encrypter;

    public function __construct(UserRepositoryInterface $repository, EncryptionInterface $encrypter)
    {
        $this->repository = $repository;
        $this->encrypter = $encrypter;
    }

    public function insert(mixed $userName, mixed $passWord, mixed $isActive): User
    {
        $user = new User();

        try {
            $user->validateUserName($userName);
            $user->validatePassWord($passWord);
            $user->validateIsActive($isActive);
            $user->setUserName($userName);
            $user->setPassword($passWord);
            $user->setIsActive($isActive);
            $validatedUserName = $user->getUserName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedUserName($validatedUserName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome do usuário a ser inserido já existe no repositório!'
                );
            }
            $plainPassword = $user->getPassWord();
            $encodedPassword = $this->encrypter->encrypt($plainPassword);
            $user->setPassword($encodedPassword);
            $user = $this->repository->insert($user);
            return $user;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            DatabaseDuplicatedEntryException |
            PDOException |
            EntityInvalidValueException $e
        ) {
            throw $e;
        }
    }

    public function update(mixed $id, mixed $userName, mixed $passWord, mixed $isActive): bool
    {
        $user = new User();

        try {
            $user->validateId($id);
            $user->validateUserName($userName);
            $user->validatePassWord($passWord);
            $user->validateIsActive($isActive);
            $user->setId($id);
            $user->setUserName($userName);
            $user->setPassword($passWord);
            $user->setIsActive($isActive);
            /*
            $validatedUserName = $user->getUserName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedUserName($validatedUserName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome do usuário a ser atualizado já existe no repositório!'
                );
            }*/
            $plainPassword = $user->getPassWord();
            $encodedPassword = $this->encrypter->encrypt($plainPassword);
            $user->setPassword($encodedPassword);
            $wasSomeUpdateHappened = $this->repository->update($user);
            return $wasSomeUpdateHappened;
        } catch (
            EntityInvalidValueException |
            DatabaseDuplicatedEntryException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function setIsActive(mixed $id, mixed $isActive): bool
    {
        $user = new User();

        try {
            $user->validateId($id);
            $user->validateIsActive($isActive);
            $user->setId($id);
            $user->setIsActive($isActive);
            $wasTheUpdateSuccessful = $this->repository->setIsActive($id, $isActive);
            return $wasTheUpdateSuccessful;
        } catch (
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function findById(mixed $id): User|null
    {
        $user = new User();

        try {
            $user->validateId($id);
            $user->setId($id);
            $user = $this->repository->findById($id);
            return $user;
        } catch (
            EntityInvalidValueException |
            DatabaseFetchFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function findByUserName(mixed $userName): User|null
    {
        $user = new User();

        try {
            $user->validateUserName($userName);
            $user->setUserName($userName);
            $user = $this->repository->findByUserName($userName);
            return $user;
        } catch (
            EntityInvalidValueException |
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
            $users = $this->repository->findAll();
            return $users;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }
}
