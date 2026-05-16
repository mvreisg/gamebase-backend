<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Sector\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Collection\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
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

    public function insert(Sector $sector, string $token): Sector
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Sector,
                PermissionType::Create
            );

            $this->sectorDomainService->ensureNameIsUnique(
                null,
                $sector->getName()
            );

            $this->sectorDomainService->ensureValueIsUnique(
                null,
                $sector->getSectorValue()
            );

            $insertedSector = $this->repository->insert($sector);

            return $insertedSector;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting sector", [
                "exception" => $e,
                "sector" => $sector,
            ]);
            throw $e;
        }
    }

    public function update(Sector $sector, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Sector,
                PermissionType::Update
            );

            $this->sectorDomainService->ensureSectorExists(
                $sector->getId()
            );

            $this->sectorDomainService->ensureNameIsUnique(
                $sector->getId(),
                $sector->getName()
            );

            $this->sectorDomainService->ensureValueIsUnique(
                $sector->getId(),
                $sector->getSectorValue()
            );

            $wasUpdated = $this->repository->update($sector);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating sector", [
                "exception" => $e,
                "sector" => $sector,
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
