<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Exception;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;

class GameGenreService
{
    private GameGenreRepositoryInterface $repository;

    public function __construct(GameGenreRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(int $genreId, int $gameId): GameGenre
    {
        $gameGenre = new GameGenre();
        $gameGenre->setGenreId($genreId);
        $gameGenre->setGameId($gameId);

        try {
            $gameGenre = $this->repository->insert($gameGenre);
            return $gameGenre;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function edit(int $genreId, int $gameId): bool
    {
        $gameGenre = new GameGenre();
        $gameGenre->setGenreId($genreId);
        $gameGenre->setGameId($gameId);

        try {
            $wasItSuccessful = $this->repository->edit($gameGenre);
            return $wasItSuccessful;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function delete(int $genreId, int $gameId): bool
    {
        $gameGenre = new GameGenre();
        $gameGenre->setGenreId($genreId);
        $gameGenre->setGameId($gameId);

        try {
            $wasItSuccessful = $this->repository->delete($gameGenre);
            return $wasItSuccessful;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function deleteAllByGameId(int $gameId): bool
    {
        $gameGenre = new GameGenre();
        $gameGenre->setGameId($gameId);

        try {
            $wasItSuccessful = $this->repository->deleteAllByGameId($gameGenre);
            return $wasItSuccessful;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findAllGameGenresByGameId(int $gameId): array
    {
        try {
            $gameGenres = $this->repository->findAllGameGenresByGameId($gameId);
            return $gameGenres;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function intersectionBetweenGameAndGameGenreByGameId(): array
    {
        try {
            $gameGenres = $this->repository->innerJoinBetweenGameAndGameGenreByGameId();
            return $gameGenres;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
