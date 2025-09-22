<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\GameEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockDuplicatedEntryException;

class MockGameEntityRepository implements GameEntityRepositoryInterface
{
    private array $data;
    private int $index;

    public function __construct()
    {
        $this->data = [];
        $this->index = 0;
    }

    public function insert(GameEntity $gameEntity): GameEntity
    {
        $this->index++;
        $gameEntity->setId($this->index);
        $this->data[] = $gameEntity;
        $newGameEntity = new GameEntity(
            $gameEntity->getId(),
            $gameEntity->getName(),
            $gameEntity->getIsActive()
        );
        return $newGameEntity;
    }

    public function update(GameEntity $gameEntity): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $gameEntity->getId()) {
                $index = $key;
            }
        }

        if ($index < 0) {
            return false;
        }

        $this->data[$index] = new GameEntity(
            $gameEntity->getId(),
            $gameEntity->getName(),
            $gameEntity->getIsActive()
        );

        return true;
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $index = $key;
            }
        }

        if ($index < 0) {
            return false;
        }

        $foundGameEntity = $this->data[$index];

        $wasUpdated =
            $foundGameEntity->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $wasUpdated;
    }

    public function findById(int $id): GameEntity|null
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

    public function checkDuplicatedNames(string $name): void
    {
        $array = array_filter(
            $this->data,
            fn (GameEntity $gameEntity) => strcmp($gameEntity->getName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedEntryException(
                $name
            );
        }
    }
}
