<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Game\Game;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedEntryException;

class MockGameRepository implements GameRepositoryInterface
{
    private array $data;
    private int $index;

    public function __construct()
    {
        $this->data = [];
        $this->index = 0;
    }

    public function insert(Game $game): Game
    {
        $this->index++;
        $game->setId($this->index);
        $this->data[] = $game;
        $newGameEntity = new Game(
            $game->getId(),
            $game->getName(),
            $game->getIsActive()
        );
        return $newGameEntity;
    }

    public function update(Game $game): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $game->getId()) {
                $index = $key;
            }
        }

        if ($index < 0) {
            return false;
        }

        $this->data[$index] = new Game(
            $game->getId(),
            $game->getName(),
            $game->getIsActive()
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

    public function findById(int $id): Game
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
            fn (Game $game) => strcmp($game->getName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedEntryException(
                $name
            );
        }
    }
}
