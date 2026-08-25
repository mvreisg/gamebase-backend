<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Permission\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\Permission\Service\Dto\PermissionServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\Permission\Service\Dto\PermissionServiceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Collection\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Permission\Repository\Dto\PermissionRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\Permission\Repository\Dto\PermissionRepositoryInterfaceUpdateDto;
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

    public function insert(PermissionServiceInsertDto $dto, string $token): Permission
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Permission,
                PermissionType::Create
            );

            $this->permissionDomainService->ensureNameIsUnique(
                null,
                $dto->name
            );

            $this->permissionDomainService->ensureValueIsUnique(
                null,
                $dto->value
            );

            $insertedPermission = $this->repository->insert(
                new PermissionRepositoryInterfaceInsertDto(
                    $dto->name,
                    $dto->value,
                    $dto->isActive
                )
            );

            return $insertedPermission;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting permission", [
                "error" => $e->getMessage(),
                "dto" => $dto,
            ]);
            throw $e;
        }
    }

    public function update(PermissionServiceUpdateDto $dto, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Permission,
                PermissionType::Update
            );

            $this->permissionDomainService->ensurePermissionExists(
                $dto->id
            );

            $this->permissionDomainService->ensureNameIsUnique(
                $dto->id,
                $dto->name
            );

            $this->permissionDomainService->ensureValueIsUnique(
                $dto->id,
                $dto->value
            );

            $wasUpdated = $this->repository->update(
                new PermissionRepositoryInterfaceUpdateDto(
                    $dto->id,
                    $dto->name,
                    $dto->value,
                    $dto->isActive
                )
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating permission", [
                "error" => $e->getMessage(),
                "dto" => $dto,
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
