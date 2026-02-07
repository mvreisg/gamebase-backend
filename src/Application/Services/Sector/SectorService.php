<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Sector;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Data\Sector;
use Mvreisg\GamebaseBackend\Domain\Data\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;

class SectorService
{
    private SectorRepositoryInterface $repository;

    public function __construct(SectorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(Sector $sector): Sector
    {
        try {
            $this->repository->checkDuplicatedNames(
                Name::make($sector->getNameValue())
            );

            $insertedSector = $this->repository->insert($sector);

            return $insertedSector;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(Sector $sector): bool
    {
        try {
            $this->repository->checkIfExists(
                Id::make($sector->getIdValue())
            );

            $this->repository->checkDuplicatedNames(
                Name::make($sector->getNameValue())
            );

            $wasUpdated = $this->repository->update($sector);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        try {
            $this->repository->checkIfExists($id);

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): Sector
    {
        try {
            $fetchedSector = $this->repository->findById($id);

            return $fetchedSector;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): SectorCollection
    {
        try {
            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
