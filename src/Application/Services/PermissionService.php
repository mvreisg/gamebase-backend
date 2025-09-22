<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Application\Exceptions\Repositories\RepositoryException;
use Mvreisg\GamebaseBackend\Domain\Entities\PermissionEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionEntityRepositoryInterface;

class PermissionService
{
    private PermissionEntityRepositoryInterface $repository;

    public function __construct(PermissionEntityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(string $name, bool $isActive): PermissionEntity
    {
        try {
            $permissionEntity = new PermissionEntity(
                PHP_INT_MAX,
                $name,
                $isActive
            );

            $permissionEntity->validateId();
            $permissionEntity->validateName();

            $validatedName = $permissionEntity->getName();

            $this->repository->checkDuplicatedNames($validatedName);

            $insertedPermissionEntity = $this->repository->insert($permissionEntity);

            return $insertedPermissionEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(int $id, string $name, bool $isActive): bool
    {
        try {
            $permissionEntity = new PermissionEntity(
                $id,
                $name,
                $isActive
            );

            $permissionEntity->validateId();
            $permissionEntity->validateName();

            /*
            $validatedName = $permission->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome da permissão a ser atualizada já existe no repositório!'
                );
            }
            */

            $wasUpdated = $this->repository->update($permissionEntity);
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $permissionEntity = new PermissionEntity(
                $id,
                '',
                $isActive
            );

            $permissionEntity->validateId();

            $validatedId = $permissionEntity->getId();
            $validatedIsActive = $permissionEntity->getIsActive();

            $wasUpdated = $this->repository->setIsActive(
                $validatedId,
                $validatedIsActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): PermissionEntity
    {
        try {
            $permissionEntity = new PermissionEntity(
                $id
            );

            $permissionEntity->validateId();

            $validatedId = $permissionEntity->getId();

            $fetchedPermissionEntity = $this->repository->findById($validatedId);

            return $fetchedPermissionEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $fetchedPermissionEntities = $this->repository->findAll();

            return $fetchedPermissionEntities;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
