<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Sector\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\Sector\Service\Dto\SectorServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\Sector\Service\Dto\SectorServiceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Collection\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Sector\Repository\Dto\SectorRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\Sector\Repository\Dto\SectorRepositoryInterfaceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Sector\Repository\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Sector\Service\SectorDomainService;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Psr\Log\LoggerInterface;

class SectorService
{
    private SectorRepositoryInterface $repository;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private SectorDomainService $sectorDomainService;
    private ClockInterface $clock;
    private LoggerInterface $logger;

    public function __construct(
        SectorRepositoryInterface $repository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        SectorDomainService $sectorDomainService,
        ClockInterface $clock,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->sectorDomainService = $sectorDomainService;
        $this->clock = $clock;
        $this->logger = $logger;
    }

    public function insert(SectorServiceInsertDto $dto, string $token): Sector
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::Sector),
                PermissionValue::from(PermissionType::Create)
            );

            $this->sectorDomainService->ensureNameIsUniqueOnInsert(
                $dto->name
            );

            $this->sectorDomainService->ensureValueIsUnique(
                $dto->value
            );

            $insertedSector = $this->repository->insert(
                new SectorRepositoryInterfaceInsertDto(
                    $dto->name,
                    $dto->value,
                    $dto->isActive
                )
            );

            $this->logger->notice("Sector inserted succesfully!", [
                "id" => $insertedSector->getId()->getValue(),
                "name" => $insertedSector->getName()->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $insertedSector;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting Sector!", [
                "exception" => $e->getMessage(),
                "name" => $dto->name->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function update(SectorServiceUpdateDto $dto, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::Sector),
                PermissionValue::from(PermissionType::Update)
            );

            $this->sectorDomainService->ensureSectorExists(
                $dto->id
            );

            $this->sectorDomainService->ensureNameIsUniqueOnUpdate(
                $dto->name,
                $dto->id
            );

            $this->sectorDomainService->ensureValueIsUnique(
                $dto->value
            );

            $wasUpdated = $this->repository->update(
                new SectorRepositoryInterfaceUpdateDto(
                    $dto->id,
                    $dto->name,
                    $dto->value,
                    $dto->isActive
                )
            );

            $this->logger->notice("Sector data updated succesfully!", [
                "wasUpdated" => $wasUpdated,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating Sector!", [
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
                SectorValue::from(SectorType::Sector),
                PermissionValue::from(PermissionType::Activate)
            );

            $this->sectorDomainService->ensureSectorExists(
                $id
            );

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            $this->logger->notice("Sector isActive updated succesfully!", [
                "wasUpdated" => $wasUpdated,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error setting Sector active status!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "isActive" => $isActive,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?Sector
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::Sector),
                PermissionValue::from(PermissionType::List)
            );

            $fetchedSector = $this->repository->findById($id);

            if ($fetchedSector === null) {
                $this->logger->notice("Sector not found!", [
                    "id" => $id->getValue(),
                    "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
                ]);
                return null;
            }

            $this->logger->notice("Sector found by id succesfully!", [
                "id" => $fetchedSector->getId()->getValue(),
                "name" => $fetchedSector->getName()->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $fetchedSector;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding Sector by id!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function findAll(string $token): ?SectorCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::Sector),
                PermissionValue::from(PermissionType::List)
            );

            $sectors = $this->repository->findAll();

            if ($sectors === null) {
                $this->logger->notice("No Sectors found!", [
                    "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
                ]);
                return null;
            }

            $this->logger->notice("All Sectors found succesfully!", [
                "count" => $sectors->count(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $sectors;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding all Sectors!", [
                "exception" => $e->getMessage(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }
}
