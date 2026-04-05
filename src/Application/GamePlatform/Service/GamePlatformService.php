<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\GamePlatform\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Game\Service\GameDomainService;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Entity\Collection\GamePlatformCollection;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Entity\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Repository\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Service\GamePlatformDomainService;
use Mvreisg\GamebaseBackend\Domain\Platform\Service\PlatformDomainService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class GamePlatformService
{
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private GameDomainService $gameDomainService;
    private PlatformDomainService $platformDomainService;
    private GamePlatformDomainService $gamePlatformDomainService;
    private GamePlatformRepositoryInterface $repository;

    public function __construct(
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GameDomainService $gameDomainService,
        PlatformDomainService $platformDomainService,
        GamePlatformDomainService $gamePlatformDomainService,
        GamePlatformRepositoryInterface $repository
    ) {
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->gameDomainService = $gameDomainService;
        $this->platformDomainService = $platformDomainService;
        $this->gamePlatformDomainService = $gamePlatformDomainService;
        $this->repository = $repository;
    }

    public function insert(GamePlatform $gamePlatform, string $token): GamePlatform
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::GamePlatform,
                PermissionType::Create
            );

            $this->gameDomainService->ensureGameExists(
                $gamePlatform->getGame()->getId()
            );

            $this->platformDomainService->ensurePlatformExists(
                $gamePlatform->getPlatform()->getId()
            );

            $insertedGamePlatform = $this->repository->insert($gamePlatform);

            return $insertedGamePlatform;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(GamePlatform $gamePlatform, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::GamePlatform,
                PermissionType::Update
            );

            $this->gameDomainService->ensureGameExists(
                $gamePlatform->getGame()->getId()
            );

            $this->platformDomainService->ensurePlatformExists(
                $gamePlatform->getPlatform()->getId()
            );

            $this->gamePlatformDomainService->ensureGamePlatformExists(
                $gamePlatform->getId()
            );

            $wasUpdated = $this->repository->update($gamePlatform);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(Id $id, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::GamePlatform,
                PermissionType::Delete
            );

            $wasDeleted = $this->repository->delete($id);

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?GamePlatform
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::GamePlatform,
                PermissionType::List
            );

            $fetchedGamePlatform = $this->repository->findById(
                $id
            );

            return $fetchedGamePlatform;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(string $token): ?GamePlatformCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::GamePlatform,
                PermissionType::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
