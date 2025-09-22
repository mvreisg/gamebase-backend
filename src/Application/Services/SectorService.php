<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Application\Exceptions\Repositories\RepositoryException;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorEntityRepositoryInterface;

class SectorService
{
    private SectorEntityRepositoryInterface $repository;

    public function __construct(SectorEntityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(string $name, bool $isActive): SectorEntity
    {
        $sectorEntity = new SectorEntity(
            PHP_INT_MAX,
            $name,
            $isActive
        );

        try {
            $sectorEntity->validateId();
            $sectorEntity->validateName();

            $validatedName = $sectorEntity->getName();

            $this->repository->checkDuplicatedNames($validatedName);

            $insertedSectorEntity = $this->repository->insert($sectorEntity);

            return $insertedSectorEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(int $id, string $name, bool $isActive): bool
    {
        try {
            $sectorEntity = new SectorEntity(
                $id,
                $name,
                $isActive
            );

            $sectorEntity->validateId();
            $sectorEntity->validateName();

            /*
            $validatedName = $sector->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome do setor a ser atualizado já existe no repositório!'
                );
            }
            */

            $wasUpdated = $this->repository->update($sectorEntity);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $sectorEntity = new SectorEntity(
                $id,
                '',
                $isActive
            );

            $sectorEntity->validateId();

            $validatedId = $sectorEntity->getId();
            $validatedIsActive = $sectorEntity->getIsActive();

            $wasUpdated = $this->repository->setIsActive(
                $validatedId,
                $validatedIsActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): SectorEntity
    {
        try {
            $sectorEntity = new SectorEntity(
                $id
            );

            $sectorEntity->validateId();

            $validatedId = $sectorEntity->getId();

            $fetchedSectorEntity = $this->repository->findById($validatedId);

            return $fetchedSectorEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $fetchedSectorEntities = $this->repository->findAll();

            return $fetchedSectorEntities;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
