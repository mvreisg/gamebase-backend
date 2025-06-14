<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformRepositoryInterface;

class MockGamePlatformRepository implements GamePlatformRepositoryInterface
{
    private array $data;
    private int $index;

    public function __construct()
    {
        $this->data = [];
        $this->index = 0;
    }

    public function insert(GamePlatform $gamePlatform): GamePlatform
    {
        $this->index++;
        $gamePlatform->setId($this->index);
        $this->data[] = $gamePlatform;
        $newGamePlatform = new GamePlatform();
        $newGamePlatform->setId($gamePlatform->getId());
        $newGamePlatform->setGameId($gamePlatform->getGameId());
        $newGamePlatform->setPlatformId($gamePlatform->getPlatformId());
        return $newGamePlatform;
    }

    public function update(GamePlatform $gamePlatform): bool
    {
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $gamePlatform->getId()) {
                $index = $key;
            }
        }

        if ($index === null) {
            return false;
        }

        $modifiedGamePlatform = $this->data[$index];

        $modifiedGamePlatform->setId($gamePlatform->getId());
        $modifiedGamePlatform->setGameId($gamePlatform->getGameId());
        $modifiedGamePlatform->setPlatformId($gamePlatform->getPlatformId());

        $this->data[$index] = $modifiedGamePlatform;

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

        if ($index > -1) {
            unset($this->data[$index]);
            return true;
        } else {
            return false;
        }
    }

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

    public function findById(int $id): GamePlatform|null
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
