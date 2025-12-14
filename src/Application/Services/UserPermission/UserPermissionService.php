<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\UserPermission;

use Mvreisg\GamebaseBackend\Application\Services\UserPermission\Exceptions\UserPermissionServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\UserPermission\Exceptions\UserPermissionServiceInvalidPermissionIdException;
use Mvreisg\GamebaseBackend\Application\Services\UserPermission\Exceptions\UserPermissionServiceInvalidUserIdException;
use Mvreisg\GamebaseBackend\Application\Services\UserPermission\Exceptions\UserPermissionServiceUnexistantPermissionException;
use Mvreisg\GamebaseBackend\Application\Services\UserPermission\Exceptions\UserPermissionServiceUnexistantUserException;
use Mvreisg\GamebaseBackend\Application\Services\UserPermission\Exceptions\UserPermissionServiceUnexistantUserPermissionException;
use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission\Exceptions\UserPermissionInvalidIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission\Exceptions\UserPermissionInvalidPermissionIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission\Exceptions\UserPermissionInvalidUserIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission\UserPermission;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;

class UserPermissionService
{
    private UserRepositoryInterface $userRepository;
    private PermissionRepositoryInterface $permissionRepository;
    private UserPermissionRepositoryInterface $userPermissionRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PermissionRepositoryInterface $permissionRepository,
        UserPermissionRepositoryInterface $userPermissionRepository
    ) {
        $this->userRepository = $userRepository;
        $this->permissionRepository = $permissionRepository;
        $this->userPermissionRepository = $userPermissionRepository;
    }

    public function insert(int $userId, int $permissionId): UserPermission
    {
        try {
            $userPermission = new UserPermission(
                null,
                $userId,
                $permissionId
            );

            $userPermission->validateUserId();
            $userPermission->validatePermissionId();

            try {
                $validatedUserId = $userPermission->getUserId();

                $this->userRepository->checkIfExists($validatedUserId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new UserPermissionServiceUnexistantUserException(
                    "User permission service error: User repository: {$e->getMessage()}",
                    $e
                );
            }

            try {
                $validatedPermissionId = $userPermission->getPermissionId();

                $this->permissionRepository->checkIfExists($validatedPermissionId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new UserPermissionServiceUnexistantPermissionException(
                    "User permission service error: Permission repository: {$e->getMessage()}",
                    $e
                );
            }

            $insertedUserPermission = $this->userPermissionRepository->insert($userPermission);

            return $insertedUserPermission;
        } catch (
            UserPermissionServiceUnexistantUserException |
            UserPermissionServiceUnexistantPermissionException
            $e
        ) {
            throw $e;
        } catch (UserPermissionInvalidUserIdException $e) {
            throw new UserPermissionServiceInvalidUserIdException(
                "User permission service error: {$e->getMessage()}",
                $e
            );
        } catch (UserPermissionInvalidPermissionIdException $e) {
            throw new UserPermissionServiceInvalidPermissionIdException(
                "User permission service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
        $e) {
            throw $e;
        }
    }

    public function update(int $id, int $userId, int $permissionId): bool
    {
        try {
            $userPermission = new UserPermission(
                $id,
                $userId,
                $permissionId
            );

            $userPermission->validateId();
            $userPermission->validateUserId();
            $userPermission->validatePermissionId();

            $validatedId = $userPermission->getId();

            $this->userPermissionRepository->checkIfExists($validatedId);

            try {
                $validatedUserId = $userPermission->getUserId();

                $this->userRepository->checkIfExists($validatedUserId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new UserPermissionServiceUnexistantUserException(
                    "User permission service error: User repository: {$e->getMessage()}",
                    $e
                );
            }

            try {
                $validatedPermissionId = $userPermission->getPermissionId();

                $this->permissionRepository->checkIfExists($validatedPermissionId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new UserPermissionServiceUnexistantPermissionException(
                    "User permission service error: Permission repository: {$e->getMessage()}",
                    $e
                );
            }

            $wasUpdated = $this->userPermissionRepository->update($userPermission);

            return $wasUpdated;
        } catch (
            UserPermissionServiceUnexistantUserException |
            UserPermissionServiceUnexistantPermissionException
        $e) {
            throw $e;
        } catch (UserPermissionInvalidIdException $e) {
            throw new UserPermissionServiceInvalidIdException(
                "User permission service error: {$e->getMessage()}",
                $e
            );
        } catch (UserPermissionInvalidUserIdException $e) {
            throw new UserPermissionServiceInvalidUserIdException(
                "User permission service error: {$e->getMessage()}",
                $e
            );
        } catch (UserPermissionInvalidPermissionIdException $e) {
            throw new UserPermissionServiceInvalidPermissionIdException(
                "User permission service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new UserPermissionServiceUnexistantUserPermissionException(
                "User permission service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
        $e) {
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $userPermission = new UserPermission(
                $id
            );

            $userPermission->validateId();

            $validatedId = $userPermission->getId();

            $this->userPermissionRepository->checkIfExists($validatedId);

            $wasDeleted = $this->userPermissionRepository->delete($userPermission);

            return $wasDeleted;
        } catch (UserPermissionInvalidIdException $e) {
            throw new UserPermissionServiceInvalidIdException(
                "User permission service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new UserPermissionServiceUnexistantUserPermissionException(
                "User permission service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
        $e) {
            throw $e;
        }
    }

    public function findById(int $id): UserPermission
    {
        try {
            $userPermission = new UserPermission(
                $id
            );

            $userPermission->validateId();

            $validatedId = $userPermission->getId();

            $fetchedUserPermission = $this->userPermissionRepository->findById(
                $validatedId
            );

            return $fetchedUserPermission;
        } catch (UserPermissionInvalidIdException $e) {
            throw new UserPermissionServiceInvalidIdException(
                "User permission service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new UserPermissionServiceUnexistantUserPermissionException(
                "User permission service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable

        $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            return $this->userPermissionRepository->findAll();
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable

        $e) {
            throw $e;
        }
    }
}
