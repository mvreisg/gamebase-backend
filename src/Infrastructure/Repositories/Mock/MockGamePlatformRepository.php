<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockGamePlatformRepository implements GamePlatformRepositoryInterface
{
    private array $data;
    private int $index;
    private GameRepositoryInterface $gameEntityRepository;
    private PlatformRepositoryInterface $platformEntityRepository;

    public function __construct(
        GameRepositoryInterface $gameEntityRepository,
        PlatformRepositoryInterface $platformEntityRepository
    ) {
        $this->data = [];
        $this->index = 0;
        $this->gameEntityRepository = $gameEntityRepository;
        $this->platformEntityRepository = $platformEntityRepository;
    }

    public function insert(GamePlatform $gamePlatform): GamePlatform
    {
        $gameId = $gamePlatform->getGameId();
        $game = $this->gameEntityRepository->findById($gameId);
        if ($game === null) {
            throw new MockUnexistantRegisterException(
                "gameId: $gameId"
            );
        }

        $platformId = $gamePlatform->getPlatformId();
        $platform = $this->platformEntityRepository->findById($platformId);
        if ($platform === null) {
            throw new MockUnexistantRegisterException(
                "platformId: $platformId"
            );
        }

        $this->index++;
        $id = $this->index;

        $gamePlatform->setId($id);
        $this->data[] = $gamePlatform;

        $newGamePlatformEntity = new GamePlatform(
            $id,
            $gameId,
            $platformId
        );
        return $newGamePlatformEntity;
    }

    public function update(GamePlatform $gamePlatform): bool
    {
        $gameId = $gamePlatform->getGameId();
        $game = $this->gameEntityRepository->findById($gameId);
        if ($game === null) {
            throw new MockUnexistantRegisterException(
                "gameId: $gameId"
            );
        }

        $platformId = $gamePlatform->getPlatformId();
        $platform = $this->platformEntityRepository->findById($platformId);
        if ($platform === null) {
            throw new MockUnexistantRegisterException(
                "platformId: $platformId"
            );
        }

        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $gamePlatform->getId()) {
                $index = $key;
                break;
            }
        }

        $gamePlatformId = $gamePlatform->getId();
        if ($index < 0) {
            throw new MockUnexistantRegisterException(
                "gamePlatformId: $gamePlatformId"
            );
        }

        $gamePlatformEntityToUpdate = $this->data[$index];

        $hasDifferentGameId =
            $gamePlatformEntityToUpdate->getGameId() !== $gamePlatform->getGameId();

        $hasDifferentPlatformId =
            $gamePlatformEntityToUpdate->getPlatformId() !== $gamePlatform->getPlatformId();

        $hasDifferences = $hasDifferentGameId || $hasDifferentPlatformId;

        if ($hasDifferences === false) {
            return false;
        }

        $this->data[$index] = new GamePlatform(
            $gamePlatformId,
            $gamePlatform->getPlatformId(),
            $gamePlatform->getGameId()
        );

        return true;
    }

    public function delete(GamePlatform $gamePlatform): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $gamePlatform->getId()) {
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

    public function findById(int $id): GamePlatform
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
