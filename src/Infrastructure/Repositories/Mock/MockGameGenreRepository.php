<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockGameGenreRepository implements GameGenreRepositoryInterface
{
    private array $data;
    private int $idIndex;

    public function __construct()
    {
        $this->data = [];
        $this->idIndex = 0;
    }

    public function insert(GameGenre $gameGenre): GameGenre
    {
        try {
            $this->idIndex++;
            $gameGenre->setId($this->idIndex);
            $this->data[] = $gameGenre;
            return new GameGenre(
                $gameGenre->getId(),
                $gameGenre->getGameId(),
                $gameGenre->getGenreId()
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(GameGenre $gameGenre): bool
    {
        try {
            $index = -1;
            foreach ($this->data as $key => $value) {
                if ($value->getId() === $gameGenre->getId()) {
                    $index = $key;
                    break;
                }
            }

            if ($index < 0) {
                return false;
            }

            $foundGameGenre = $this->data[$index];

            $hasDifferentGameId =
                $foundGameGenre->getGameId() !== $gameGenre->getGameId();

            $hasDifferentGenreId =
                $foundGameGenre->getGenreId() !== $gameGenre->getGenreId();

            $isDifferent = $hasDifferentGameId || $hasDifferentGenreId;

            if ($isDifferent === false) {
                return false;
            }

            $this->data[$index] = new GameGenre(
                $gameGenre->getId(),
                $gameGenre->getGameId(),
                $gameGenre->getGenreId()
            );

            return true;
        } catch (\Throwable $e) {
            throw $e;
        }
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

        if ($index < 0) {
            return false;
        }

        unset($this->data[$index]);
        return true;
    }

    public function findById(int $id): GameGenre
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant game genre with id $id"
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
            "Unexistant game genre with id $id"
        );
    }
}
