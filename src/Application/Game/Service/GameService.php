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
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Psr\Log\LoggerInterface;

class GameService
{
    private GameRepositoryInterface $repository;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private GameDomainService $gameDomainService;
    private ClockInterface $clock;
    private LoggerInterface $logger;

    public function __construct(
        GameRepositoryInterface $repository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GameDomainService $gameDomainService,
        ClockInterface $clock,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->gameDomainService = $gameDomainService;
        $this->clock = $clock;
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

            $this->logger->notice("Game inserted succesfully!", [
                "id" => $insertedGame->getId()->getValue(),
                "name" => $insertedGame->getName()->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $insertedGame;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting Game!", [
                "exception" => $e->getMessage(),
                "name" => $dto->name->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
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

            $this->logger->notice("Game data updated succesfully!", [
                "wasUpdated" => $wasUpdated,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating Game!", [
                "exception" => $e->getMessage(),
                "name" => $dto->name->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
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

            $this->logger->notice("Game isActive updated succesfully!", [
                "wasUpdated" => $wasUpdated,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error setting Game active status!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "isActive" => $isActive,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
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

            $fetchedGame = $this->repository->findById(
                $id
            );

            if ($fetchedGame === null) {
                return null;
            }

            $this->logger->notice("Game found by id succesfully!", [
                "id" => $fetchedGame->getId()->getValue(),
                "name" => $fetchedGame->getName()->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $fetchedGame;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding Game by id!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
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

            $games = $this->repository->findAll();

            $this->logger->notice("All Games found succesfully!", [
                "count" => $games->count(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $games;
        } catch (\Throwable $e) {
            $this->logger->error("Error finding all Games!", [
                "exception" => $e->getMessage(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }
}
