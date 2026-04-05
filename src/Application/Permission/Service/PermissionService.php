<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Permission\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Collection\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Permission\Repository\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Permission\Service\PermissionDomainService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class PermissionService
{
    private PermissionRepositoryInterface $repository;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private PermissionDomainService $permissionDomainService;

    public function __construct(
        PermissionRepositoryInterface $repository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        PermissionDomainService $permissionDomainService
    ) {
        $this->repository = $repository;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->permissionDomainService = $permissionDomainService;
    }

    public function insert(Permission $permission, string $token): Permission
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Permission,
                PermissionType::Create
            );

            $this->permissionDomainService->ensureNameIsUnique(
                $permission->getName()
            );

            $insertedPermission = $this->repository->insert($permission);

            return $insertedPermission;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(Permission $permission, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Permission,
                PermissionType::Update
            );

            $this->permissionDomainService->ensurePermissionExists(
                $permission->getId()
            );

            $this->permissionDomainService->ensureNameIsUnique(
                $permission->getName()
            );

            $wasUpdated = $this->repository->update($permission);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Permission,
                PermissionType::Activate
            );

            $this->permissionDomainService->ensurePermissionExists(
                $id
            );

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?Permission
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Permission,
                PermissionType::List
            );

            $fetchedPermission = $this->repository->findById($id);

            return $fetchedPermission;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(string $token): ?PermissionCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Permission,
                PermissionType::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
