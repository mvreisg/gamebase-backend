<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Game\Game;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedNameException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockGameRepository implements GameRepositoryInterface
{
    /**
     * @var Game[]
     */
    private array $data;
    private int $idIndex;

    public function __construct()
    {
        $this->data = [];
        $this->idIndex = 0;
    }

    public function insert(Game $game): Game
    {
        $this->idIndex++;
        $game->setId($this->idIndex);
        $this->data[] = $game;
        return new Game(
            $game->getId(),
            $game->getName(),
            $game->getIsActive()
        );
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

        $foundGame = $this->data[$index];

        $hasDifferentNames =
            $foundGame->getName() !== $game->getName();

        $hasDifferentIsActive =
            $foundGame->getIsActive() !== $game->getIsActive();

        $isDifferent = $hasDifferentNames || $hasDifferentIsActive;

        if ($isDifferent === false) {
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

        $foundGame = $this->data[$index];

        $wasUpdated = $foundGame->getIsActive() !== $isActive;

        if ($wasUpdated === false) {
            return false;
        }

        $this->data[$index]->setIsActive($isActive);

        return true;
    }

    public function findById(int $id): Game
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant game with id $id"
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
            "Unexistant game with id $id"
        );
    }

    public function checkDuplicatedNames(string $name): void
    {
        $array = array_filter(
            $this->data,
            fn (Game $game) => strcmp($game->getName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedNameException(
                "Duplicated game name: $name"
            );
        }
    }
}
