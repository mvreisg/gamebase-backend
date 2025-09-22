<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\UserPermissionEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserPermissionEntityRepositoryInterface;

class UserPermissionService
{
    private UserPermissionEntityRepositoryInterface $repository;

    public function __construct(UserPermissionEntityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(int $userId, int $permissionId): UserPermissionEntity
    {
        $userPermissionEntity = new UserPermissionEntity(
            PHP_INT_MAX,
            $userId,
            $permissionId
        );

        try {
            $userPermissionEntity->validateUserId();
            $userPermissionEntity->validatePermissionId();

            $insertedUserPermissionEntity = $this->repository->insert($userPermissionEntity);

            return $insertedUserPermissionEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(int $id, int $userId, int $permissionId): bool
    {
        $userPermissionEntity = new UserPermissionEntity(
            $id,
            $userId,
            $permissionId
        );

        try {
            $userPermissionEntity->validateId();
            $userPermissionEntity->validateUserId();
            $userPermissionEntity->validatePermissionId();

            $wasUpdated = $this->repository->update($userPermissionEntity);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $userPermissionEntity = new UserPermissionEntity(
            $id
        );

        try {
            $userPermissionEntity->validateId();

            $wasDeleted = $this->repository->delete($userPermissionEntity);

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): UserPermissionEntity|null
    {
        $userPermissionEntity = new UserPermissionEntity(
            $id
        );

        try {
            $userPermissionEntity->validateId();

            $validatedId = $userPermissionEntity->getId();

            $fetchedUserPermissionEntity = $this->repository->findById($validatedId);

            return $fetchedUserPermissionEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $fetchedUserPermissionsEntities = $this->repository->findAll();

            return $fetchedUserPermissionsEntities;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
