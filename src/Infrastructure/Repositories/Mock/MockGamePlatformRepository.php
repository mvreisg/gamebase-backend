<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockGamePlatformRepository implements GamePlatformRepositoryInterface
{
    /**
     * @var GamePlatform[]
     */
    private array $data;
    private int $idIndex;

    public function __construct()
    {
        $this->data = [];
        $this->idIndex = 0;
    }

    public function insert(GamePlatform $gamePlatform): GamePlatform
    {
        $this->idIndex++;
        $gamePlatform->setId($this->idIndex);
        $this->data[] = $gamePlatform;
        return new GamePlatform(
            $gamePlatform->getId(),
            $gamePlatform->getPlatformId(),
            $gamePlatform->getGameId()
        );
    }

    public function update(GamePlatform $gamePlatform): bool
    {
        try {
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

            $foundGamePlatform = $this->data[$index];

            $hasDifferentGameId =
                $foundGamePlatform->getGameId() !== $gamePlatform->getGameId();

            $hasDifferentPlatformId =
                $foundGamePlatform->getPlatformId() !== $gamePlatform->getPlatformId();

            $isDifferent = $hasDifferentGameId || $hasDifferentPlatformId;

            if ($isDifferent === false) {
                return false;
            }

            $this->data[$index] = new GamePlatform(
                $gamePlatform->getId(),
                $gamePlatform->getPlatformId(),
                $gamePlatform->getGameId()
            );

            return true;
        } catch (\Throwable $e) {
            throw $e;
        }
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

    public function findById(int $id): GamePlatform
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant game platform with id $id"
        );
    }

    public function findAll(): array
    {
        return $this->data;
    }

    public function checkIfExists(int $id): void
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant game platform with id $id"
        );
    }
}
