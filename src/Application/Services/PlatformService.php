<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Application\Exceptions\Repositories\RepositoryException;
use Mvreisg\GamebaseBackend\Domain\Entities\PlatformEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformEntityRepositoryInterface;

class PlatformService
{
    private PlatformEntityRepositoryInterface $repository;

    public function __construct(PlatformEntityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(string $name, bool $isActive): PlatformEntity
    {
        $platformEntity = new PlatformEntity(
            PHP_INT_MAX,
            $name,
            $isActive
        );

        try {
            $platformEntity->validateName();

            $validatedName = $platformEntity->getName();

            $this->repository->checkDuplicatedNames($validatedName);

            $insertedPlatformEntity = $this->repository->insert($platformEntity);

            return $insertedPlatformEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(int $id, string $name, bool $isActive): bool
    {
        $platformEntity = new PlatformEntity(
            $id,
            $name,
            $isActive
        );

        try {
            $platformEntity->validateId();
            $platformEntity->validateName();

            /*
            $validatedName = $platform->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome da plataforma a ser atualizada já existe no repositório!'
                );
            }
            */

            $wasUpdated = $this->repository->update($platformEntity);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $platformEntity = new PlatformEntity(
            $id,
            '',
            $isActive
        );

        try {
            $platformEntity->validateId();

            $validatedId = $platformEntity->getId();
            $validatedIsActive = $platformEntity->getIsActive();

            $wasUpdated = $this->repository->setIsActive(
                $validatedId,
                $validatedIsActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): PlatformEntity|null
    {
        $platformEntity = new PlatformEntity(
            $id
        );

        try {
            $platformEntity->validateId();

            $validatedId = $platformEntity->getId();

            $fetchedPlatformEntity = $this->repository->findById($validatedId);

            return $fetchedPlatformEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $fetchedPlatformEntity = $this->repository->findAll();

            return $fetchedPlatformEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
