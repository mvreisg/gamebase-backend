<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Platform\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\Platform\Service\Dto\PlatformServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\Platform\Service\Dto\PlatformServiceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Collection\PlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Platform;
use Mvreisg\GamebaseBackend\Domain\Platform\Repository\Dto\PlatformRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\Platform\Repository\Dto\PlatformRepositoryInterfaceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Platform\Repository\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Platform\Service\PlatformDomainService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Psr\Log\LoggerInterface;

class PlatformService
{
    private PlatformRepositoryInterface $repository;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private PlatformDomainService $platformDomainService;
    private LoggerInterface $logger;

    public function __construct(
        PlatformRepositoryInterface $repository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        PlatformDomainService $platformDomainService,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->platformDomainService = $platformDomainService;
        $this->logger = $logger;
    }

    public function insert(PlatformServiceInsertDto $dto, string $token): Platform
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Platform,
                PermissionType::Create
            );

            $this->platformDomainService->ensureNameIsUniqueOnInsert(
                $dto->name
            );

            $insertedPlatform = $this->repository->insert(
                new PlatformRepositoryInterfaceInsertDto(
                    $dto->name,
                    $dto->isActive
                )
            );

            return $insertedPlatform;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting platform", [
                "error" => $e->getMessage(),
                "dto" => $dto,
            ]);
            throw $e;
        }
    }

    public function update(PlatformServiceUpdateDto $dto, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Platform,
                PermissionType::Update
            );

            $this->platformDomainService->ensurePlatformExists(
                $dto->id
            );

            $this->platformDomainService->ensureNameIsUniqueOnUpdate(
                $dto->name,
                $dto->id
            );

            $wasUpdated = $this->repository->update(
                new PlatformRepositoryInterfaceUpdateDto(
                    $dto->id,
                    $dto->name,
                    $dto->isActive
                )
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating platform", [
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
                SectorType::Platform,
                PermissionType::Activate
            );

            $this->platformDomainService->ensurePlatformExists(
                $id
            );

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error setting platform active status", [
                "error" => $e->getMessage(),
                "platformId" => $id,
                "isActive" => $isActive,
            ]);
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?Platform
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Platform,
                PermissionType::List
            );

            $fetchedPlatform = $this->repository->findById($id);

            return $fetchedPlatform;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding platform by ID", [
                "error" => $e->getMessage(),
                "platformId" => $id,
            ]);
            throw $e;
        }
    }

    public function findAll(string $token): ?PlatformCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Platform,
                PermissionType::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            $this->logger->error("Error finding all platforms", [
                "error" => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
