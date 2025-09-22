<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenreEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockUnexistantRegisterException;

class MockGameGenreEntityRepository implements GameGenreEntityRepositoryInterface
{
    private array $data;
    private int $id;
    private GameEntityRepositoryInterface $gameEntityRepository;
    private GenreEntityRepositoryInterface $genreEntityRepository;

    public function __construct(
        GameEntityRepositoryInterface $gameEntityRepository,
        GenreEntityRepositoryInterface $genreEntityRepository
    ) {
        $this->data = [];
        $this->id = 0;
        $this->gameEntityRepository = $gameEntityRepository;
        $this->genreEntityRepository = $genreEntityRepository;
    }

    public function insert(GameGenreEntity $gameGenreEntity): GameGenreEntity
    {
        try {
            $gameId = $gameGenreEntity->getGameId();
            $game = $this->gameEntityRepository->findById($gameId);
            if ($game === null) {
                throw new MockUnexistantRegisterException(
                    $gameId
                );
            }

            $genreId = $gameGenreEntity->getGenreId();
            $genre = $this->genreEntityRepository->findById($genreId);
            if ($genre === null) {
                throw new MockUnexistantRegisterException(
                    $genreId
                );
            }

            $this->id++;
            $id = $this->id;
            $gameGenreEntity->setId($id);
            $this->data[] = $gameGenreEntity;
            $newGameGenreEntity = new GameGenreEntity(
                $id,
                $genreId,
                $gameId
            );
            return $newGameGenreEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(GameGenreEntity $gameGenreEntity): bool
    {
        try {
            $gameId = $gameGenreEntity->getGameId();
            $game = $this->gameEntityRepository->findById($gameId);
            if ($game === null) {
                throw new MockUnexistantRegisterException(
                    $gameId
                );
            }

            $genreId = $gameGenreEntity->getGenreId();
            $genre = $this->genreEntityRepository->findById($genreId);
            if ($genre === null) {
                throw new MockUnexistantRegisterException(
                    $genreId
                );
            }

            $index = -1;
            $idToSearch = $gameGenreEntity->getId();
            foreach ($this->data as $key => $value) {
                if ($value->getId() === $idToSearch) {
                    $index = $key;
                    break;
                }
            }

            if ($index < 0) {
                throw new MockUnexistantRegisterException(
                    $idToSearch
                );
            }

            $gameGenreEntityToUpdate = $this->data[$index];

            $hasDifferendGameId =
                $gameGenreEntityToUpdate->getGameId() !== $gameGenreEntity->getGameId();

            $hasDifferendGenreId =
                $gameGenreEntityToUpdate->getGenreId() !== $gameGenreEntity->getGenreId();

            $isDifferent = $hasDifferendGameId || $hasDifferendGenreId;

            if ($isDifferent === false) {
                return false;
            }

            $this->data[$index] = new GameGenreEntity(
                $gameGenreEntity->getId(),
                $gameGenreEntity->getGameId(),
                $gameGenreEntity->getGenreId()
            );

            return true;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(GameGenreEntity $gameGenreEntity): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $gameGenreEntity->getId()) {
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

    public function findById(int $id): GameGenreEntity|null
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
