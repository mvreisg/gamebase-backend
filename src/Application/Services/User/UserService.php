<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\User;

use Mvreisg\GamebaseBackend\Application\Services\User\Exceptions\UserServiceDuplicatedUsernameException;
use Mvreisg\GamebaseBackend\Application\Services\User\Exceptions\UserServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\User\Exceptions\UserServiceInvalidPasswordException;
use Mvreisg\GamebaseBackend\Application\Services\User\Exceptions\UserServiceInvalidUsernameException;
use Mvreisg\GamebaseBackend\Application\Services\User\Exceptions\UserServiceUnexistantUserException;
use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\Exceptions\EncryptionException;
use Mvreisg\GamebaseBackend\Domain\Entities\User\Exceptions\UserInvalidIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\User\Exceptions\UserInvalidPasswordException;
use Mvreisg\GamebaseBackend\Domain\Entities\User\Exceptions\UserInvalidUsernameException;
use Mvreisg\GamebaseBackend\Domain\Entities\User\User;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryDuplicatedUsernameException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;

class UserService
{
    private UserRepositoryInterface $repository;
    private EncryptionInterface $encrypter;

    public function __construct(
        UserRepositoryInterface $repository,
        EncryptionInterface $encrypter
    ) {
        $this->repository = $repository;
        $this->encrypter = $encrypter;
    }

    public function insert(string $username, string $password, bool $isActive): User
    {
        try {
            $user = new User(
                null,
                $username,
                $password,
                $isActive
            );

            $user->validateUsername();
            $user->validatePassword();

            $validatedUserName = $user->getUsername();
            $validatedPassword = $user->getPassword();

            $this->repository->checkDuplicatedUserNames($validatedUserName);

            $encodedPassword = $this->encrypter->encrypt($validatedPassword);
            $user->setPassword($encodedPassword);
            $insertedUser = $this->repository->insert($user);

            return $insertedUser;
        } catch (UserInvalidUsernameException $e) {
            throw new UserServiceInvalidUsernameException(
                "User service error: {$e->getMessage()}",
                $e
            );
        } catch (UserInvalidPasswordException $e) {
            throw new UserServiceInvalidPasswordException(
                "User service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryDuplicatedUsernameException $e) {
            throw new UserServiceDuplicatedUsernameException(
                "User service error: {$e->getMessage()}",
                $e
            );
        } catch (
            EncryptionException |
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function update(int $id, string $username, string $password, bool $isActive): bool
    {
        try {
            $user = new User(
                $id,
                $username,
                $password,
                $isActive
            );

            $user->validateId();

            $validatedId = $user->getId();
            $this->repository->checkIfExists($validatedId);

            $user->validateUsername();
            $user->validatePassword();

            $validatedUserName = $user->getUsername();
            $this->repository->checkDuplicatedUserNames($validatedUserName);

            $validatedPassword = $user->getPassword();
            $encodedPassword = $this->encrypter->encrypt($validatedPassword);
            $user->setPassword($encodedPassword);
            $wasUpdated = $this->repository->update($user);

            return $wasUpdated;
        } catch (UserInvalidIdException $e) {
            throw new UserServiceInvalidIdException(
                "User service error: {$e->getMessage()}",
                $e
            );
        } catch (UserInvalidUsernameException $e) {
            throw new UserServiceInvalidUsernameException(
                "User service error: {$e->getMessage()}",
                $e
            );
        } catch (UserInvalidPasswordException $e) {
            throw new UserServiceInvalidPasswordException(
                "User service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryDuplicatedUsernameException $e) {
            throw new UserServiceDuplicatedUsernameException(
                "User service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new UserServiceUnexistantUserException(
                "User service error: {$e->getMessage()}",
                $e
            );
        } catch (
            EncryptionException |
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $user = new User(
                $id,
                null,
                null,
                $isActive
            );

            $user->validateId();

            $validatedId = $user->getId();

            $this->repository->checkIfExists($validatedId);

            $validatedIsActive = $user->getIsActive();

            $wasUpdated = $this->repository->setIsActive($validatedId, $validatedIsActive);

            return $wasUpdated;
        } catch (UserInvalidIdException $e) {
            throw new UserServiceInvalidIdException(
                "User service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new UserServiceUnexistantUserException(
                "User service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function findById(int $id): User
    {
        try {
            $user = new User(
                $id
            );

            $user->validateId();

            $validatedId = $user->getId();

            $fetchedUser = $this->repository->findById($validatedId);

            return $fetchedUser;
        } catch (UserInvalidIdException $e) {
            throw new UserServiceInvalidIdException(
                "User service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new UserServiceUnexistantUserException(
                "User service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function findByUsername(string $username): ?User
    {
        try {
            $user = new User(
                null,
                $username
            );

            $user->validateUsername();

            $validatedUserName = $user->getUsername();

            $fetchedUser = $this->repository->findByUsername($validatedUserName);

            return $fetchedUser;
        } catch (UserInvalidUsernameException $e) {
            throw new UserServiceInvalidUsernameException(
                "User service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new UserServiceUnexistantUserException(
                "User service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            return $this->repository->findAll();
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }
}
