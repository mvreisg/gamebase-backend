<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Game;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseUnexistantValueException;

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
        $newGame = new Game();
        $newGame->setId($game->getId());
        $newGame->setName($game->getName());
        $newGame->setIsActive($game->getIsActive());
        return $newGame;
    }

    public function update(Game $game): bool
    {
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $game->getId()) {
                $index = $key;
            }
        }

        if ($index === null) {
            throw new DatabaseUnexistantValueException(
                'O jogo com o id ' . $game->getId() . ' não existe no repositório!'
            );
        }

        $modifiedGame = $this->data[$index];

        $modifiedGame->setId($game->getId());
        $modifiedGame->setName($game->getName());
        $modifiedGame->setIsActive($game->getIsActive());

        $this->data[$index] = $modifiedGame;

        return true;
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
            throw new DatabaseUnexistantValueException(
                'O jogo com o id ' . $id . ' não existe no repositório!'
            );
        }

        $findedGame = $this->data[$index];

        $changedSomething = $findedGame->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $changedSomething;
    }

    public function findById(int $id): Game|null
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

    public function hasDuplicatedNames(string $name): bool
    {
        $array = array_filter($this->data, function (Game $game) use ($name) {
            return strcmp($game->getName(), $name) === 0;
        });
        return count($array) > 0;
    }
}
