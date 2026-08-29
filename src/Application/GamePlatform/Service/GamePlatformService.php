<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\GamePlatform\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\GamePlatform\Service\Dto\GamePlatformServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\GamePlatform\Service\Dto\GamePlatformServiceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Game\Service\GameDomainService;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Repository\Dto\GamePlatformRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Entity\Collection\GamePlatformCollection;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Entity\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Repository\Dto\GamePlatformRepositoryInterfaceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Repository\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Service\GamePlatformDomainService;
use Mvreisg\GamebaseBackend\Domain\Platform\Service\PlatformDomainService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Psr\Log\LoggerInterface;

class GamePlatformService
{
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private GameDomainService $gameDomainService;
    private PlatformDomainService $platformDomainService;
    private GamePlatformDomainService $gamePlatformDomainService;
    private GamePlatformRepositoryInterface $repository;
    private LoggerInterface $logger;

    public function __construct(
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GameDomainService $gameDomainService,
        PlatformDomainService $platformDomainService,
        GamePlatformDomainService $gamePlatformDomainService,
        GamePlatformRepositoryInterface $repository,
        LoggerInterface $logger
    ) {
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->gameDomainService = $gameDomainService;
        $this->platformDomainService = $platformDomainService;
        $this->gamePlatformDomainService = $gamePlatformDomainService;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function insert(GamePlatformServiceInsertDto $dto, string $token): GamePlatform
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::GamePlatform,
                PermissionType::Create
            );

            $this->gameDomainService->ensureGameExists(
                $dto->gameId
            );

            $this->platformDomainService->ensurePlatformExists(
                $dto->platformId
            );

            $insertedGamePlatform = $this->repository->insert(
                new GamePlatformRepositoryInterfaceInsertDto(
                    $dto->gameId,
                    $dto->platformId
                )
            );

            return $insertedGamePlatform;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting GamePlatform", [
                "exception" => $e,
                "dto" => $dto,
            ]);
            throw $e;
        }
    }

    public function update(GamePlatformServiceUpdateDto $dto, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::GamePlatform,
                PermissionType::Update
            );

            $this->gameDomainService->ensureGameExists(
                $dto->gameId
            );

            $this->platformDomainService->ensurePlatformExists(
                $dto->platformId
            );

            $this->gamePlatformDomainService->ensureGamePlatformExists(
                $dto->id
            );

            $wasUpdated = $this->repository->update(
                new GamePlatformRepositoryInterfaceUpdateDto(
                    $dto->id,
                    $dto->gameId,
                    $dto->platformId
                )
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating GamePlatform", [
                "exception" => $e,
                "dto" => $dto,
            ]);
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

            $this->gamePlatformDomainService->ensureGamePlatformExists(
                $id
            );

            $wasDeleted = $this->repository->delete($id);

            return $wasDeleted;
        } catch (\Throwable $e) {
            $this->logger->error("Error deleting GamePlatform", [
                "exception" => $e,
                "gamePlatformId" => $id,
            ]);
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
            $this->logger->error("Error finding GamePlatform by ID", [
                "exception" => $e,
                "gamePlatformId" => $id,
            ]);
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
            $this->logger->error("Error finding all GamePlatforms", [
                "exception" => $e,
            ]);
            throw $e;
        }
    }
}
