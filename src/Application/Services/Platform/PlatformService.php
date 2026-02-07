<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Platform;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Data\Platform;
use Mvreisg\GamebaseBackend\Domain\Data\PlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PlatformRepositoryInterface;

class PlatformService
{
    private PlatformRepositoryInterface $repository;

    public function __construct(PlatformRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(Platform $platform): Platform
    {
        try {
            $this->repository->checkDuplicatedNames(
                Name::make($platform->getNameValue())
            );

            $insertedPlatform = $this->repository->insert($platform);

            return $insertedPlatform;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(Platform $platform): bool
    {
        try {
            $this->repository->checkIfExists(
                Id::make($platform->getIdValue())
            );

            $this->repository->checkDuplicatedNames(
                Name::make($platform->getNameValue())
            );

            $wasUpdated = $this->repository->update($platform);

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

    public function findById(Id $id): Platform
    {
        try {
            $fetchedPlatform = $this->repository->findById($id);

            return $fetchedPlatform;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): PlatformCollection
    {
        try {
            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
