<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Game\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\Game\Service\Dto\GameServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\Game\Service\Dto\GameServiceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Game\Entity\Collection\GameCollection;
use Mvreisg\GamebaseBackend\Domain\Game\Entity\Game;
use Mvreisg\GamebaseBackend\Domain\Game\Repository\Dto\GameRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\Game\Repository\Dto\GameRepositoryInterfaceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Game\Repository\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Game\Service\GameDomainService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Psr\Log\LoggerInterface;

class GameService
{
    private GameRepositoryInterface $repository;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private GameDomainService $gameDomainService;
    private LoggerInterface $logger;

    public function __construct(
        GameRepositoryInterface $repository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GameDomainService $gameDomainService,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->gameDomainService = $gameDomainService;
        $this->logger = $logger;
    }

    public function insert(GameServiceInsertDto $dto, string $token): Game
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Game,
                PermissionType::Create
            );

            $this->gameDomainService->ensureNameIsUniqueOnInsert(
                $dto->name
            );

            $insertedGame = $this->repository->insert(
                new GameRepositoryInterfaceInsertDto(
                    $dto->name,
                    $dto->isActive
                )
            );

            return $insertedGame;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting game", [
                "exception" => $e,
                "data" => $dto,
            ]);
            throw $e;
        }
    }

    public function update(GameServiceUpdateDto $dto, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Game,
                PermissionType::Update
            );

            $this->gameDomainService->ensureGameExists(
                $dto->id
            );

            $this->gameDomainService->ensureNameIsUniqueOnUpdate(
                $dto->name,
                $dto->id
            );

            $wasUpdated = $this->repository->update(
                new GameRepositoryInterfaceUpdateDto(
                    $dto->id,
                    $dto->name,
                    $dto->isActive
                )
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating game", [
                "exception" => $e,
                "dto" => $dto,
            ]);
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Game,
                PermissionType::Activate
            );

            $this->gameDomainService->ensureGameExists(
                $id
            );

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error setting game active status", [
                "exception" => $e,
                "gameId" => $id,
                "isActive" => $isActive,
            ]);
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?Game
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Game,
                PermissionType::List
            );

            $foundGame = $this->repository->findById(
                $id
            );

            return $foundGame;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding game", [
                "exception" => $e,
                "gameId" => $id,
            ]);
            throw $e;
        }
    }

    public function findAll(string $token): ?GameCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Game,
                PermissionType::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            $this->logger->error("Error finding games", [
                "exception" => $e,
            ]);
            throw $e;
        }
    }
}
