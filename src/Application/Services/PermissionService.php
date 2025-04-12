<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\Permission;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use PDOException;

class PermissionService
{
    private PermissionRepositoryInterface $repository;

    public function __construct(PermissionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(string $name, bool $isActive): Permission
    {
        try {
            $permission = new Permission(0, $name, $isActive);
            $permission->validateId();
            $permission->validateName();
            $validatedName = $permission->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException('O nome da permissão a ser inserida já existe no repositório!');
            }

            $permissionCopy = $this->repository->insert($permission);
            return $permissionCopy;
        } catch (
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            EntityInvalidValueException |
            PDOException $e) {
                throw $e;
            }
    }

    public function update(int $id, string $name, bool $isActive): bool
    {
        try {
            $permission = new Permission($id, $name, $isActive);
            $permission->validateId();
            $permission->validateName();
            $validatedName = $permission->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException('O nome da permissão a ser atualizada já existe no repositório!');
            }

            $wasTheUpdateSuccessful = $this->repository->update($permission);
            return $wasTheUpdateSuccessful;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseDuplicatedEntryException |
            EntityInvalidValueException |
            PDOException $e) {
                throw $e;
            }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $permission = new Permission($id, '', $isActive);
            $permission->validateId();

            $wasSomethingChanged = $this->repository->setIsActive($id, $isActive);
            return $wasSomethingChanged;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            EntityInvalidValueException |
            PDOException $e) {
                throw $e;
            }
    }

    public function findById(int $id): Permission
    {
        try {
            $permission = new Permission($id);
            $permission->validateId();

            $fetchedPermission = $this->repository->findById($id);
            return $fetchedPermission;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            EntityInvalidValueException |
            PDOException $e) {
                throw $e;
            }
    }

    public function findAll(): array
    {
        try {
            $fetchedPermissions = $this->repository->findAll();
            return $fetchedPermissions;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e) {
                throw $e;
            }
    }
}
