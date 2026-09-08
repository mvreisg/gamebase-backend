<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Platform\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\Platform\Service\Dto\PlatformServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\Platform\Service\Dto\PlatformServiceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Collection\PlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Platform;
use Mvreisg\GamebaseBackend\Domain\Platform\Repository\Dto\PlatformRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\Platform\Repository\Dto\PlatformRepositoryInterfaceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Platform\Repository\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Platform\Service\PlatformDomainService;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Psr\Log\LoggerInterface;

class PlatformService
{
    private PlatformRepositoryInterface $repository;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private PlatformDomainService $platformDomainService;
    private ClockInterface $clock;
    private LoggerInterface $logger;

    public function __construct(
        PlatformRepositoryInterface $repository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        PlatformDomainService $platformDomainService,
        ClockInterface $clock,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->platformDomainService = $platformDomainService;
        $this->clock = $clock;
        $this->logger = $logger;
    }

    public function insert(PlatformServiceInsertDto $dto, string $token): Platform
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::Platform),
                PermissionValue::from(PermissionType::Create)
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

            $this->logger->notice("Platform inserted succesfully!", [
                "id" => $insertedPlatform->getId()->getValue(),
                "name" => $insertedPlatform->getName()->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $insertedPlatform;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting Platform!", [
                "exception" => $e->getMessage(),
                "name" => $dto->name->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function update(PlatformServiceUpdateDto $dto, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::Platform),
                PermissionValue::from(PermissionType::Update)
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

            $this->logger->notice("Platform data updated succesfully!", [
                "wasUpdated" => $wasUpdated,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating Platform!", [
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
                SectorValue::from(SectorType::Platform),
                PermissionValue::from(PermissionType::Activate)
            );

            $this->platformDomainService->ensurePlatformExists(
                $id
            );

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            $this->logger->notice("Platform isActive updated succesfully!", [
                "wasUpdated" => $wasUpdated,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error setting Platform active status!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "isActive" => $isActive,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?Platform
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::Platform),
                PermissionValue::from(PermissionType::List)
            );

            $fetchedPlatform = $this->repository->findById($id);

            if ($fetchedPlatform === null) {
                $this->logger->notice("Platform not found!", [
                    "id" => $id->getValue(),
                    "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
                ]);
                return null;
            }

            $this->logger->notice("Platform found by id succesfully!", [
                "id" => $fetchedPlatform->getId()->getValue(),
                "name" => $fetchedPlatform->getName()->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $fetchedPlatform;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding Platform by id!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function findAll(string $token): ?PlatformCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::Platform),
                PermissionValue::from(PermissionType::List)
            );

            $platforms = $this->repository->findAll();

            if ($platforms === null) {
                $this->logger->notice("No Platforms found!", [
                    "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
                ]);
                return null;
            }

            $this->logger->notice("All Platforms found succesfully!", [
                "count" => $platforms->count(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $platforms;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding all Platforms!", [
                "exception" => $e->getMessage(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }
}
