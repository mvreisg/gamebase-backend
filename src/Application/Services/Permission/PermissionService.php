<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Permission;

use Mvreisg\GamebaseBackend\Application\Services\Permission\Exceptions\PermissionServiceDuplicatedNameException;
use Mvreisg\GamebaseBackend\Application\Services\Permission\Exceptions\PermissionServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\Permission\Exceptions\PermissionServiceInvalidNameException;
use Mvreisg\GamebaseBackend\Application\Services\Permission\Exceptions\PermissionServiceUnexistantPermissionException;
use Mvreisg\GamebaseBackend\Domain\Entities\Permission\Exceptions\PermissionInvalidIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\Permission\Exceptions\PermissionInvalidNameException;
use Mvreisg\GamebaseBackend\Domain\Entities\Permission\Permission;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryDuplicatedNameException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;

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
            $permission = new Permission(
                null,
                $name,
                $isActive
            );

            $permission->validateName();

            $validatedName = $permission->getName();

            $this->repository->checkDuplicatedNames($validatedName);

            $insertedPermission = $this->repository->insert($permission);

            return $insertedPermission;
        } catch (PermissionInvalidNameException $e) {
            throw new PermissionServiceInvalidNameException(
                "Permission service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryDuplicatedNameException $e) {
            throw new PermissionServiceDuplicatedNameException(
                "Permission service error: {$e->getMessage()}",
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

    public function update(int $id, string $name, bool $isActive): bool
    {
        try {
            $permission = new Permission(
                $id,
                $name,
                $isActive
            );

            $permission->validateId();

            $validatedId = $permission->getId();
            $this->repository->checkIfExists($validatedId);

            $permission->validateName();

            $validatedName = $permission->getName();
            $this->repository->checkDuplicatedNames($validatedName);

            $wasUpdated = $this->repository->update($permission);
            return $wasUpdated;
        } catch (PermissionInvalidIdException $e) {
            throw new PermissionServiceInvalidIdException(
                "Permission service error: {$e->getMessage()}",
                $e
            );
        } catch (PermissionInvalidNameException $e) {
            throw new PermissionServiceInvalidNameException(
                "Permission service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryDuplicatedNameException $e) {
            throw new PermissionServiceDuplicatedNameException(
                "Permission service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new PermissionServiceUnexistantPermissionException(
                "Permission service error: {$e->getMessage()}",
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

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $permission = new Permission(
                $id,
                null,
                $isActive
            );

            $permission->validateId();

            $validatedId = $permission->getId();
            $this->repository->checkIfExists($validatedId);

            $validatedIsActive = $permission->getIsActive();

            $wasUpdated = $this->repository->setIsActive(
                $validatedId,
                $validatedIsActive
            );

            return $wasUpdated;
        } catch (PermissionInvalidIdException $e) {
            throw new PermissionServiceInvalidIdException(
                "Permission service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new PermissionServiceUnexistantPermissionException(
                "Permission service error: {$e->getMessage()}",
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

    public function findById(int $id): Permission
    {
        try {
            $permission = new Permission(
                $id
            );

            $permission->validateId();

            $validatedId = $permission->getId();

            $fetchedPermission = $this->repository->findById($validatedId);

            return $fetchedPermission;
        } catch (PermissionInvalidIdException $e) {
            throw new PermissionServiceInvalidIdException(
                "Permission service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new PermissionServiceUnexistantPermissionException(
                "Permission service error: {$e->getMessage()}",
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
