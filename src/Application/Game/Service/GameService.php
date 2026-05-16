<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Game\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Game\Entity\Collection\GameCollection;
use Mvreisg\GamebaseBackend\Domain\Game\Entity\Game;
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

    public function insert(Game $game, string $token): Game
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Game,
                PermissionType::Create
            );

            $this->gameDomainService->ensureNameIsUnique(
                null,
                $game->getName()
            );

            $insertedGame = $this->repository->insert($game);

            return $insertedGame;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting game", [
                "exception" => $e,
                "game" => $game,
            ]);
            throw $e;
        }
    }

    public function update(Game $game, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Game,
                PermissionType::Update
            );

            $this->gameDomainService->ensureGameExists(
                $game->getId()
            );

            $this->gameDomainService->ensureNameIsUnique(
                $game->getId(),
                $game->getName()
            );

            $wasUpdated = $this->repository->update($game);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating game", [
                "exception" => $e,
                "game" => $game,
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
