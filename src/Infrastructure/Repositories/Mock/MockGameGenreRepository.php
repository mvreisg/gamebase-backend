<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockGameGenreRepository implements GameGenreRepositoryInterface
{
    private array $data;
    private int $id;
    private GameRepositoryInterface $gameEntityRepository;
    private GenreRepositoryInterface $genreEntityRepository;

    public function __construct(
        GameRepositoryInterface $gameEntityRepository,
        GenreRepositoryInterface $genreEntityRepository
    ) {
        $this->data = [];
        $this->id = 0;
        $this->gameEntityRepository = $gameEntityRepository;
        $this->genreEntityRepository = $genreEntityRepository;
    }

    public function insert(GameGenre $gameGenre): GameGenre
    {
        try {
            $gameId = $gameGenre->getGameId();
            $game = $this->gameEntityRepository->findById($gameId);
            if ($game === null) {
                throw new MockUnexistantRegisterException(
                    "gameId: $gameId"
                );
            }

            $genreId = $gameGenre->getGenreId();
            $genre = $this->genreEntityRepository->findById($genreId);
            if ($genre === null) {
                throw new MockUnexistantRegisterException(
                    "genreId: $genreId"
                );
            }

            $this->id++;
            $id = $this->id;
            $gameGenre->setId($id);
            $this->data[] = $gameGenre;
            $newGameGenreEntity = new GameGenre(
                $id,
                $genreId,
                $gameId
            );
            return $newGameGenreEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(GameGenre $gameGenre): bool
    {
        try {
            $gameId = $gameGenre->getGameId();
            $game = $this->gameEntityRepository->findById($gameId);
            if ($game === null) {
                throw new MockUnexistantRegisterException(
                    "gameId: $gameId"
                );
            }

            $genreId = $gameGenre->getGenreId();
            $genre = $this->genreEntityRepository->findById($genreId);
            if ($genre === null) {
                throw new MockUnexistantRegisterException(
                    "genreId: $genreId"
                );
            }

            $index = -1;
            $idToSearch = $gameGenre->getId();
            foreach ($this->data as $key => $value) {
                if ($value->getId() === $idToSearch) {
                    $index = $key;
                    break;
                }
            }

            if ($index < 0) {
                throw new MockUnexistantRegisterException(
                    "idToSearch: $idToSearch"
                );
            }

            $gameGenreEntityToUpdate = $this->data[$index];

            $hasDifferendGameId =
                $gameGenreEntityToUpdate->getGameId() !== $gameGenre->getGameId();

            $hasDifferendGenreId =
                $gameGenreEntityToUpdate->getGenreId() !== $gameGenre->getGenreId();

            $isDifferent = $hasDifferendGameId || $hasDifferendGenreId;

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

        $findedGameGenre = $this->data[$index];

        $changedSomething = $findedGameGenre->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $changedSomething;
    }
    */

    public function findById(int $id): GameGenre
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
