<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\GameGenre;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenreCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GenreRepositoryInterface;

class GameGenreService
{
    private GameGenreRepositoryInterface $gameGenreRepository;
    private GameRepositoryInterface $gameRepository;
    private GenreRepositoryInterface $genreRepository;

    public function __construct(
        GameGenreRepositoryInterface $gameGenreRepository,
        GameRepositoryInterface $gameRepository,
        GenreRepositoryInterface $genreRepository
    ) {
        $this->gameGenreRepository = $gameGenreRepository;
        $this->gameRepository = $gameRepository;
        $this->genreRepository = $genreRepository;
    }

    public function insert(GameGenre $gameGenre): GameGenre
    {
        try {
            $this->gameRepository->checkIfExists(
                $gameGenre->getGameId()
            );

            $this->genreRepository->checkIfExists(
                $gameGenre->getGenreId()
            );

            $insertedGameGenre = $this->gameGenreRepository->insert($gameGenre);

            return $insertedGameGenre;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(GameGenre $gameGenre): bool
    {
        try {
            $this->gameGenreRepository->checkIfExists(
                $gameGenre->getId()
            );

            $this->gameRepository->checkIfExists(
                $gameGenre->getGameId()
            );

            $this->genreRepository->checkIfExists(
                $gameGenre->getGenreId()
            );

            $wasUpdated = $this->gameGenreRepository->update($gameGenre);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(Id $id): bool
    {
        try {
            $this->gameGenreRepository->checkIfExists($id);

            $wasDeleted = $this->gameGenreRepository->delete($id);

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): GameGenre
    {
        try {
            $fetchedGameGenre = $this->gameGenreRepository->findById(
                $id
            );

            return $fetchedGameGenre;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): GameGenreCollection
    {
        try {
            return $this->gameGenreRepository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
