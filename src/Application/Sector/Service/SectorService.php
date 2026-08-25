<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Sector\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\Sector\Service\Dto\SectorServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\Sector\Service\Dto\SectorServiceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Collection\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Sector\Repository\Dto\SectorRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\Sector\Repository\Dto\SectorRepositoryInterfaceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Sector\Repository\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Sector\Service\SectorDomainService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Psr\Log\LoggerInterface;

class SectorService
{
    private SectorRepositoryInterface $repository;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private SectorDomainService $sectorDomainService;
    private LoggerInterface $logger;

    public function __construct(
        SectorRepositoryInterface $repository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        SectorDomainService $sectorDomainService,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->sectorDomainService = $sectorDomainService;
        $this->logger = $logger;
    }

    public function insert(SectorServiceInsertDto $dto, string $token): Sector
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Sector,
                PermissionType::Create
            );

            $this->sectorDomainService->ensureNameIsUnique(
                null,
                $dto->name
            );

            $this->sectorDomainService->ensureValueIsUnique(
                null,
                $dto->value
            );

            $insertedSector = $this->repository->insert(
                new SectorRepositoryInterfaceInsertDto(
                    $dto->name,
                    $dto->value,
                    $dto->isActive
                )
            );

            return $insertedSector;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting sector", [
                "exception" => $e,
                "dto" => $dto,
            ]);
            throw $e;
        }
    }

    public function update(SectorServiceUpdateDto $dto, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Sector,
                PermissionType::Update
            );

            $this->sectorDomainService->ensureSectorExists(
                $dto->id
            );

            $this->sectorDomainService->ensureNameIsUnique(
                $dto->id,
                $dto->name
            );

            $this->sectorDomainService->ensureValueIsUnique(
                $dto->id,
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

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating sector", [
                "exception" => $e,
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
                SectorType::Sector,
                PermissionType::Activate
            );

            $this->sectorDomainService->ensureSectorExists(
                $id
            );

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error setting sector active status", [
                "exception" => $e,
                "sectorId" => $id,
                "isActive" => $isActive,
            ]);
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?Sector
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Sector,
                PermissionType::List
            );

            $fetchedSector = $this->repository->findById($id);

            return $fetchedSector;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding sector by id", [
                "exception" => $e,
                "sectorId" => $id,
            ]);
            throw $e;
        }
    }

    public function findAll(string $token): ?SectorCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Sector,
                PermissionType::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            $this->logger->error("Error finding all sectors", [
                "exception" => $e,
            ]);
            throw $e;
        }
    }
}
