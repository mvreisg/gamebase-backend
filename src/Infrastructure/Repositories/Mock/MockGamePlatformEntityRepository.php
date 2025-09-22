<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatformEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockUnexistantRegisterException;

class MockGamePlatformEntityRepository implements GamePlatformEntityRepositoryInterface
{
    private array $data;
    private int $index;
    private GameEntityRepositoryInterface $gameEntityRepository;
    private PlatformEntityRepositoryInterface $platformEntityRepository;

    public function __construct(
        GameEntityRepositoryInterface $gameEntityRepository,
        PlatformEntityRepositoryInterface $platformEntityRepository
    ) {
        $this->data = [];
        $this->index = 0;
        $this->gameEntityRepository = $gameEntityRepository;
        $this->platformEntityRepository = $platformEntityRepository;
    }

    public function insert(GamePlatformEntity $gamePlatformEntity): GamePlatformEntity
    {
        $gameId = $gamePlatformEntity->getGameId();
        $game = $this->gameEntityRepository->findById($gameId);
        if ($game === null) {
            throw new MockUnexistantRegisterException(
                $gameId
            );
        }

        $platformId = $gamePlatformEntity->getPlatformId();
        $platform = $this->platformEntityRepository->findById($platformId);
        if ($platform === null) {
            throw new MockUnexistantRegisterException(
                $platformId
            );
        }

        $this->index++;
        $id = $this->index;

        $gamePlatformEntity->setId($id);
        $this->data[] = $gamePlatformEntity;

        $newGamePlatformEntity = new GamePlatformEntity(
            $id,
            $gameId,
            $platformId
        );
        return $newGamePlatformEntity;
    }

    public function update(GamePlatformEntity $gamePlatformEntity): bool
    {
        $gameId = $gamePlatformEntity->getGameId();
        $game = $this->gameEntityRepository->findById($gameId);
        if ($game === null) {
            throw new MockUnexistantRegisterException(
                $gameId
            );
        }

        $platformId = $gamePlatformEntity->getPlatformId();
        $platform = $this->platformEntityRepository->findById($platformId);
        if ($platform === null) {
            throw new MockUnexistantRegisterException(
                $platformId
            );
        }

        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $gamePlatformEntity->getId()) {
                $index = $key;
                break;
            }
        }

        if ($index < 0) {
            throw new MockUnexistantRegisterException(
                $gamePlatformEntity->getId()
            );
        }

        $gamePlatformEntityToUpdate = $this->data[$index];

        $hasDifferentGameId =
            $gamePlatformEntityToUpdate->getGameId() !== $gamePlatformEntity->getGameId();

        $hasDifferentPlatformId =
            $gamePlatformEntityToUpdate->getPlatformId() !== $gamePlatformEntity->getPlatformId();

        $hasDifferences = $hasDifferentGameId || $hasDifferentPlatformId;

        if ($hasDifferences === false) {
            return false;
        }

        $this->data[$index] = new GamePlatformEntity(
            $gamePlatformEntity->getId(),
            $gamePlatformEntity->getPlatformId(),
            $gamePlatformEntity->getGameId()
        );

        return true;
    }

    public function delete(GamePlatformEntity $gamePlatformEntity): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $gamePlatformEntity->getId()) {
                $index = $key;
                break;
            }
        }

        if ($index < 0) {
            return false;
        }

        unset($this->data[$index]);
        return true;
    }

    /*
    public function setIsActive(int $id, bool $isActive): bool
    {
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $index = $key;
            }
        }

        if ($index === null) {
            return false;
        }

        $findedGamePlatform = $this->data[$index];

        $changedSomething = $findedGamePlatform->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $changedSomething;
    }
    */

    public function findById(int $id): GamePlatformEntity|null
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        return null;
    }

    public function findAll(): array
    {
        return $this->data;
    }
}
