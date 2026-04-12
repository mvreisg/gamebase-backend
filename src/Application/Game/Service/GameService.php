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

class GameService
{
    private GameRepositoryInterface $repository;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private GameDomainService $gameDomainService;

    public function __construct(
        GameRepositoryInterface $repository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GameDomainService $gameDomainService,
    ) {
        $this->repository = $repository;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->gameDomainService = $gameDomainService;
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
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?Game
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Game,
                PermissionType::Activate
            );

            $foundGame = $this->repository->findById(
                $id
            );

            return $foundGame;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(string $token): ?GameCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Game,
                PermissionType::Activate
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
