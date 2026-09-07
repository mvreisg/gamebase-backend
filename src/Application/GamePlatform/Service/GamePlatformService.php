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
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Psr\Log\LoggerInterface;

class GamePlatformService
{
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private GameDomainService $gameDomainService;
    private PlatformDomainService $platformDomainService;
    private GamePlatformDomainService $gamePlatformDomainService;
    private GamePlatformRepositoryInterface $repository;
    private ClockInterface $clock;
    private LoggerInterface $logger;

    public function __construct(
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GameDomainService $gameDomainService,
        PlatformDomainService $platformDomainService,
        GamePlatformDomainService $gamePlatformDomainService,
        GamePlatformRepositoryInterface $repository,
        ClockInterface $clock,
        LoggerInterface $logger
    ) {
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->gameDomainService = $gameDomainService;
        $this->platformDomainService = $platformDomainService;
        $this->gamePlatformDomainService = $gamePlatformDomainService;
        $this->repository = $repository;
        $this->clock = $clock;
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

            $this->logger->notice("GamePlatform inserted succesfully!", [
                "id" => $insertedGamePlatform->getId()->getValue(),
                "game" => [
                    "id" => $insertedGamePlatform->getGame()->getId()->getValue()
                ],
                "platform" => [
                    "id" => $insertedGamePlatform->getPlatform()->getId()->getValue()
                ],
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $insertedGamePlatform;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting GamePlatform!", [
                "exception" => $e->getMessage(),
                "game" => [
                    "id" => $dto->gameId->getValue()
                ],
                "platform" => [
                    "id" => $dto->platformId->getValue()
                ],
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
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

            $this->logger->notice("GamePlatform data updated succesfully!", [
                "id" => $dto->id->getValue(),
                "wasUpdated" => $wasUpdated,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating GamePlatform!", [
                "exception" => $e->getMessage(),
                "game" => [
                    "id" => $dto->gameId->getValue()
                ],
                "platform" => [
                    "id" => $dto->platformId->getValue()
                ],
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
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

            $this->logger->notice("GamePlatform deleted succesfully!", [
                "id" => $id->getValue(),
                "wasDeleted" => $wasDeleted,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasDeleted;
        } catch (\Throwable $e) {
            $this->logger->error("Error deleting GamePlatform!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
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

            if ($fetchedGamePlatform === null) {
                $this->logger->notice("GamePlatform not found!", [
                    "id" => $id->getValue(),
                    "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
                ]);
                return null;
            }

            $this->logger->notice("GamePlatform found by id succesfully!", [
                "id" => $fetchedGamePlatform->getId()->getValue(),
                "game" => [
                    "id" => $fetchedGamePlatform->getGame()->getId()->getValue(),
                ],
                "platform" => [
                    "id" => $fetchedGamePlatform->getPlatform()->getId()->getValue(),
                ],
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $fetchedGamePlatform;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding GamePlatform by id", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
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

            $gamePlatforms = $this->repository->findAll();

            if ($gamePlatforms === null) {
                $this->logger->notice("No GamePlatforms found!", [
                    "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
                ]);
                return null;
            }

            $this->logger->notice("All GamePlatforms found succesfully!", [
                "count" => $gamePlatforms->count(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $gamePlatforms;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding all GamePlatforms", [
                "exception" => $e->getMessage(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }
}
