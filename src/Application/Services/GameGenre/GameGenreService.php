<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\GameGenre;

use Mvreisg\GamebaseBackend\Domain\Data\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Data\GameGenreCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
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
                Id::make($gameGenre->getGameIdValue())
            );

            $this->genreRepository->checkIfExists(
                Id::make($gameGenre->getGenreIdValue())
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
                Id::make($gameGenre->getIdValue())
            );

            $this->gameRepository->checkIfExists(
                Id::make($gameGenre->getGameIdValue())
            );

            $this->genreRepository->checkIfExists(
                Id::make($gameGenre->getGenreIdValue())
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
