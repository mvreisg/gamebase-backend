<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use PDOException;
use Throwable;

class UserPermissionService
{
    private UserPermissionRepositoryInterface $repository;

    public function __construct(UserPermissionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(int $userId, int $permissionId): UserPermission
    {
        $userPermission = new UserPermission(
            PHP_INT_MAX,
            $userId,
            $permissionId
        );

        try {
            $userPermission->validateUserId();
            $userPermission->validatePermissionId();

            $userPermission = $this->repository->insert($userPermission);

            return $userPermission;
        } catch (
            EntityInvalidValueException |
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException |
            Throwable $e
        ) {
            throw $e;
        }
    }

    public function update(int $id, int $userId, int $permissionId): bool
    {
        $userPermission = new UserPermission(
            $id,
            $userId,
            $permissionId
        );

        try {
            $userPermission->validateId();
            $userPermission->validateUserId();
            $userPermission->validatePermissionId();

            $wasTheUpdateSuccessful = $this->repository->update($userPermission);

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

    public function delete(int $id): bool
    {
        $userPermission = new UserPermission(
            $id
        );

        try {
            $userPermission->validateId();

            $wasTheDeleteSuccessful = $this->repository->delete($userPermission);

            return $wasTheDeleteSuccessful;
        } catch (
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function findById(int $id): UserPermission|null
    {
        $userPermission = new UserPermission(
            $id
        );

        try {
            $userPermission->validateId();

            $userPermission = $this->repository->findById($id);

            return $userPermission;
        } catch (
            EntityInvalidValueException |
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
            $userPermissions = $this->repository->findAll();

            return $userPermissions;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }
}
