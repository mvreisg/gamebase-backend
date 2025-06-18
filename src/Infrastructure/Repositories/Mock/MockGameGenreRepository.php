<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseUnexistantRegisterException;

class MockGameGenreRepository implements GameGenreRepositoryInterface
{
    private array $data;
    private int $id;
    private GameRepositoryInterface $gameRepository;
    private GenreRepositoryInterface $genreRepository;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        GenreRepositoryInterface $genreRepository
    ) {
        $this->data = [];
        $this->id = 0;
        $this->gameRepository = $gameRepository;
        $this->genreRepository = $genreRepository;
    }

    public function insert(GameGenre $gameGenre): GameGenre
    {
        try {
            $gameId = $gameGenre->getGameId();
            $game = $this->gameRepository->findById($gameId);
            if ($game === null) {
                throw new DatabaseUnexistantRegisterException(
                    'O registro com o id ' . $gameId . ' não existe!'
                );
            }

            $genreId = $gameGenre->getGenreId();
            $genre = $this->genreRepository->findById($genreId);
            if ($genre === null) {
                throw new DatabaseUnexistantRegisterException(
                    'O registro com o id ' . $genreId . ' não existe!'
                );
            }

            $this->id++;
            $id = $this->id;
            $gameGenre->setId($id);
            $this->data[] = $gameGenre;
            $newGameGenre = new GameGenre(
                $id,
                $genreId,
                $gameId
            );
            return $newGameGenre;
        } catch (DatabaseUnexistantRegisterException $e) {
            throw $e;
        }
    }

    public function update(GameGenre $gameGenre): bool
    {
        try {
            $gameId = $gameGenre->getGameId();
            $game = $this->gameRepository->findById($gameId);
            if ($game === null) {
                throw new DatabaseUnexistantRegisterException(
                    'O registro com o id ' . $gameId . ' não existe!'
                );
            }

            $genreId = $gameGenre->getGenreId();
            $genre = $this->genreRepository->findById($genreId);
            if ($genre === null) {
                throw new DatabaseUnexistantRegisterException(
                    'O registro com o id ' . $genreId . ' não existe!'
                );
            }

            $idToUpdate = null;
            $idToSearch = $gameGenre->getId();
            foreach ($this->data as $key => $value) {
                if ($value->getId() === $idToSearch) {
                    $idToUpdate = $key;
                }
            }

            if ($idToUpdate === null) {
                throw new DatabaseUnexistantRegisterException(
                    'O registro com o id ' . $idToSearch . ' não existe!'
                );
            }

            $gameGenreToUpdate = $this->data[$idToUpdate];

            $hasDifferendGameId =
                $gameGenreToUpdate->getGameId() !== $gameGenre->getGameId();

            $hasDifferendGenreId =
                $gameGenreToUpdate->getGenreId() !== $gameGenre->getGenreId();

            $isDifferent = $hasDifferendGameId || $hasDifferendGenreId;

            if ($isDifferent === false) {
                return false;
            }

            $gameGenreToUpdate->setGameId($gameGenre->getGameId());
            $gameGenreToUpdate->getGenreId($gameGenre->getGenreId());

            $this->data[$idToUpdate] = $gameGenreToUpdate;

            return true;
        } catch (DatabaseUnexistantRegisterException $e) {
            throw $e;
        }
    }

    public function delete(GameGenre $gameGenre): bool
    {
        $idToDelete = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $gameGenre->getId()) {
                $idToDelete = $key;
                break;
            }
        }

        if ($idToDelete === null) {
            return false;
        }

        unset($this->data[$idToDelete]);
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
