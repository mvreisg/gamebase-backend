<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Permission;

use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Entities\Permission;
use Mvreisg\GamebaseBackend\Domain\Entities\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;

class PermissionService
{
    private PermissionRepositoryInterface $repository;

    public function __construct(PermissionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(Permission $permission): Permission
    {
        try {
            $this->repository->checkDuplicatedNames(
                Name::make($permission->getNameValue())
            );

            $insertedPermission = $this->repository->insert($permission);

            return $insertedPermission;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(Permission $permission): bool
    {
        try {
            $this->repository->checkIfExists(
                Id::make($permission->getIdValue())
            );

            $this->repository->checkDuplicatedNames(
                Name::make($permission->getNameValue())
            );

            $wasUpdated = $this->repository->update($permission);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        try {
            $this->repository->checkIfExists($id);

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): Permission
    {
        try {
            $fetchedPermission = $this->repository->findById($id);

            return $fetchedPermission;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): PermissionCollection
    {
        try {
            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
