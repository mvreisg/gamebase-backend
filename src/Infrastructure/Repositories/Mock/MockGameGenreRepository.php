<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;

class MockGameGenreRepository implements GameGenreRepositoryInterface
{
    private array $data;
    private int $index;

    public function __construct()
    {
        $this->data = [];
        $this->index = 0;
    }

    public function insert(GameGenre $gameGenre): GameGenre
    {
        $this->index++;
        $gameGenre->setId($this->index);
        $this->data[] = $gameGenre;
        $newGameGenre = new GameGenre();
        $newGameGenre->setId($gameGenre->getId());
        $newGameGenre->setGameId($gameGenre->getGameId());
        $newGameGenre->setGenreId($gameGenre->getGenreId());
        return $newGameGenre;
    }

    public function update(GameGenre $gameGenre): bool
    {
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $gameGenre->getId()) {
                $index = $key;
            }
        }

        if ($index === null) {
            return false;
        }

        $modifiedGameGenre = $this->data[$index];

        $modifiedGameGenre->setId($gameGenre->getId());
        $modifiedGameGenre->setGameId($gameGenre->getGameId());
        $modifiedGameGenre->setGenreId($gameGenre->getGenreId());

        $this->data[$index] = $modifiedGameGenre;

        return true;
    }

    public function delete(GameGenre $gameGenre): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $gameGenre->getId()) {
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

        $findedGameGenre = $this->data[$index];

        $changedSomething = $findedGameGenre->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $changedSomething;
    }

    public function findById(int $id): GameGenre|null
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
