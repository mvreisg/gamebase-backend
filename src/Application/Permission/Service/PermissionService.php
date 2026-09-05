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
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Psr\Log\LoggerInterface;

class PermissionService
{
    private PermissionRepositoryInterface $repository;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private PermissionDomainService $permissionDomainService;
    private ClockInterface $clock;
    private LoggerInterface $logger;

    public function __construct(
        PermissionRepositoryInterface $repository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        PermissionDomainService $permissionDomainService,
        ClockInterface $clock,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->permissionDomainService = $permissionDomainService;
        $this->clock = $clock;
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

            $this->permissionDomainService->ensureNameIsUniqueOnInsert(
                $dto->name
            );

            $this->permissionDomainService->ensureValueIsUnique(
                $dto->value
            );

            $insertedPermission = $this->repository->insert(
                new PermissionRepositoryInterfaceInsertDto(
                    $dto->name,
                    $dto->value,
                    $dto->isActive
                )
            );

            $this->logger->notice("Permission inserted succesfully!", [
                "id" => $insertedPermission->getId()->getValue(),
                "name" => $insertedPermission->getName()->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $insertedPermission;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting permission!", [
                "exception" => $e->getMessage(),
                "name" => $dto->name->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
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

            $this->permissionDomainService->ensureNameIsUniqueOnUpdate(
                $dto->name,
                $dto->id
            );

            $this->permissionDomainService->ensureValueIsUnique(
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

            $this->logger->notice("Permission data updated succesfully!", [
                "wasUpdated" => $wasUpdated,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating permission!", [
                "exception" => $e->getMessage(),
                "name" => $dto->name->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
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

            $this->logger->notice("Permission isActive updated succesfully!", [
                "wasUpdated" => $wasUpdated,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error setting permission active status!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "isActive" => $isActive,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
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

            $this->logger->notice("Permission found by ID succesfully!", [
                 "id" => $fetchedPermission->getId()->getValue(),
                 "name" => $fetchedPermission->getName()->getValue(),
                 "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
             ]);

            return $fetchedPermission;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding permission by ID!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
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

            $permissions = $this->repository->findAll();

            $this->logger->notice("All permissions found succesfully!", [
                "count" => $permissions->count(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $permissions;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding all permissions!", [
                "exception" => $e->getMessage(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }
}
