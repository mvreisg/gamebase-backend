<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Platform\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Collection\PlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Platform;
use Mvreisg\GamebaseBackend\Domain\Platform\Repository\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Platform\Service\PlatformDomainService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class PlatformService
{
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private PlatformDomainService $platformDomainService;
    private PlatformRepositoryInterface $repository;

    public function __construct(
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        PlatformDomainService $platformDomainService,
        PlatformRepositoryInterface $repository
    ) {
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->platformDomainService = $platformDomainService;
        $this->repository = $repository;
    }

    public function insert(Platform $platform, string $token): Platform
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Platform,
                PermissionType::Create
            );

            $this->platformDomainService->ensureNameIsUnique(
                $platform->getName()
            );

            $insertedPlatform = $this->repository->insert($platform);

            return $insertedPlatform;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(Platform $platform, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Platform,
                PermissionType::Update
            );

            $this->platformDomainService->ensurePlatformExists(
                $platform->getId()
            );

            $this->platformDomainService->ensureNameIsUnique(
                $platform->getName()
            );

            $wasUpdated = $this->repository->update($platform);

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
            throw $e;
        }
    }
}
