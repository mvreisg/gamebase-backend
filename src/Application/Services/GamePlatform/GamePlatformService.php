<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\GamePlatform;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PlatformRepositoryInterface;

class GamePlatformService
{
    private GameRepositoryInterface $gameRepository;
    private PlatformRepositoryInterface $platformRepository;
    private GamePlatformRepositoryInterface $gamePlatformRepository;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        PlatformRepositoryInterface $platformRepository,
        GamePlatformRepositoryInterface $gamePlatformRepository
    ) {
        $this->gameRepository = $gameRepository;
        $this->platformRepository = $platformRepository;
        $this->gamePlatformRepository = $gamePlatformRepository;
    }

    public function insert(GamePlatform $gamePlatform): GamePlatform
    {
        try {
            $this->gameRepository->checkIfExists(
                $gamePlatform->getGameId()->getValue()
            );

            $this->platformRepository->checkIfExists(
                $gamePlatform->getPlatformId()->getValue()
            );

            $insertedGamePlatform = $this->gamePlatformRepository->insert($gamePlatform);

            return $insertedGamePlatform;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(GamePlatform $gamePlatform): bool
    {
        try {
            $this->gamePlatformRepository->checkIfExists(
                $gamePlatform->getId()
            );

            $this->gameRepository->checkIfExists(
                $gamePlatform->getGameId()->getValue()
            );

            $this->platformRepository->checkIfExists(
                $gamePlatform->getPlatformId()->getValue()
            );

            $wasUpdated = $this->gamePlatformRepository->update($gamePlatform);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(Id $id): bool
    {
        try {
            $this->gamePlatformRepository->checkIfExists($id);

            $wasDeleted = $this->gamePlatformRepository->delete($id);

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): GamePlatform
    {
        try {
            $fetchedGamePlatform = $this->gamePlatformRepository->findById(
                $id
            );

            return $fetchedGamePlatform;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): GamePlatformCollection
    {
        try {
            return $this->gamePlatformRepository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
