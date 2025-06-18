<?php

declare(strict_types=1);

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
use Throwable;

class UserService
{
    private UserRepositoryInterface $repository;
    private EncryptionInterface $encrypter;

    public function __construct(UserRepositoryInterface $repository, EncryptionInterface $encrypter)
    {
        $this->repository = $repository;
        $this->encrypter = $encrypter;
    }

    public function insert(string $userName, string $passWord, bool $isActive): User
    {
        $user = new User();

        try {
            $user->setUserName($userName);
            $user->setPassword($passWord);
            $user->setIsActive($isActive);

            $user->validateUserName();
            $user->validatePassWord();

            $validatedUserName = $user->getUserName();

            $hasDuplicatedNames = $this->repository->hasDuplicatedUserNames($validatedUserName);
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
            EntityInvalidValueException |
            Throwable $e
        ) {
            throw $e;
        }
    }

    public function update(int $id, string $userName, string $passWord, bool $isActive): bool
    {
        $user = new User();

        try {
            $user->setId($id);
            $user->setUserName($userName);
            $user->setPassword($passWord);
            $user->setIsActive($isActive);

            $user->validateId();
            $user->validateUserName();
            $user->validatePassWord();

            /*
            $validatedUserName = $user->getUserName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedUserNames($validatedUserName);
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
            PDOException |
            Throwable $e
        ) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $user = new User();

        try {
            $user->setId($id);
            $user->setIsActive($isActive);

            $user->validateId();

            $wasTheUpdateSuccessful = $this->repository->setIsActive($id, $isActive);

            return $wasTheUpdateSuccessful;
        } catch (
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException |
            Throwable $e
        ) {
            throw $e;
        }
    }

    public function findById(int $id): User|null
    {
        $user = new User();

        try {
            $user->setId($id);

            $user->validateId();

            $user = $this->repository->findById($id);

            return $user;
        } catch (
            EntityInvalidValueException |
            DatabaseFetchFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException |
            Throwable $e
        ) {
            throw $e;
        }
    }

    public function findByUserName(string $userName): User|null
    {
        $user = new User();

        try {
            $user->setUserName($userName);

            $user->validateUserName();

            $user = $this->repository->findByUserName($userName);

            return $user;
        } catch (
            EntityInvalidValueException |
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
            $users = $this->repository->findAll();

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
}
