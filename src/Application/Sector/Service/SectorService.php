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

class SectorService
{
    private SectorRepositoryInterface $repository;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private SectorDomainService $sectorDomainService;

    public function __construct(
        SectorRepositoryInterface $repository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        SectorDomainService $sectorDomainService
    ) {
        $this->repository = $repository;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->sectorDomainService = $sectorDomainService;
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
                $sector->getName()
            );

            $insertedSector = $this->repository->insert($sector);

            return $insertedSector;
        } catch (\Throwable $e) {
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
                $sector->getName()
            );

            $wasUpdated = $this->repository->update($sector);

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
            throw $e;
        }
    }
}
