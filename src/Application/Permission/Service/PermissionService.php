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
use Psr\Log\LoggerInterface;

class PermissionService
{
    private PermissionRepositoryInterface $repository;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private PermissionDomainService $permissionDomainService;
    private LoggerInterface $logger;

    public function __construct(
        PermissionRepositoryInterface $repository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        PermissionDomainService $permissionDomainService,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->permissionDomainService = $permissionDomainService;
        $this->logger = $logger;
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
                null,
                $permission->getName()
            );

            $this->permissionDomainService->ensureValueIsUnique(
                null,
                $permission->getPermissionValue()
            );

            $insertedPermission = $this->repository->insert($permission);

            return $insertedPermission;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting permission", [
                "error" => $e->getMessage(),
                "permission" => $permission,
            ]);
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
                $permission->getId(),
                $permission->getName()
            );

            $this->permissionDomainService->ensureValueIsUnique(
                $permission->getId(),
                $permission->getPermissionValue()
            );

            $wasUpdated = $this->repository->update($permission);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating permission", [
                "error" => $e->getMessage(),
                "permission" => $permission,
            ]);
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
            $this->logger->error("Error setting permission active status", [
                "error" => $e->getMessage(),
                "permissionId" => $id,
                "isActive" => $isActive,
            ]);
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
            $this->logger->error("Error finding permission by ID", [
                "error" => $e->getMessage(),
                "permissionId" => $id,
            ]);
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
            $this->logger->error("Error finding all permissions", [
                "error" => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
