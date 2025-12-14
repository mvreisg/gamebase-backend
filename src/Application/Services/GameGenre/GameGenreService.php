<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\GameGenre;

use Mvreisg\GamebaseBackend\Application\Services\GameGenre\Exceptions\GameGenreServiceInvalidGameIdException;
use Mvreisg\GamebaseBackend\Application\Services\GameGenre\Exceptions\GameGenreServiceInvalidGenreIdException;
use Mvreisg\GamebaseBackend\Application\Services\GameGenre\Exceptions\GameGenreServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\GameGenre\Exceptions\GameGenreServiceUnexistantGameException;
use Mvreisg\GamebaseBackend\Application\Services\GameGenre\Exceptions\GameGenreServiceUnexistantGameGenreException;
use Mvreisg\GamebaseBackend\Application\Services\GameGenre\Exceptions\GameGenreServiceUnexistantGenreException;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre\Exceptions\GameGenreInvalidGameIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre\Exceptions\GameGenreInvalidGenreIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre\Exceptions\GameGenreInvalidIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;

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

    public function insert(int $gameId, int $genreId): GameGenre
    {
        try {
            $gameGenre = new GameGenre(
                null,
                $gameId,
                $genreId
            );

            $gameGenre->validateGameId();
            $gameGenre->validateGenreId();

            try {
                $validatedGameId = $gameGenre->getGameId();

                $this->gameRepository->checkIfExists($validatedGameId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new GameGenreServiceUnexistantGameException(
                    "Game genre service error: Game repository: {$e->getMessage()}",
                    $e
                );
            }

            try {
                $validatedGenreId = $gameGenre->getGenreId();

                $this->genreRepository->checkIfExists($validatedGenreId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new GameGenreServiceUnexistantGenreException(
                    "Game genre service error: Genre repository: {$e->getMessage()}",
                    $e
                );
            }

            $insertedGameGenre = $this->gameGenreRepository->insert($gameGenre);

            return $insertedGameGenre;
        } catch (
            GameGenreServiceUnexistantGameException |
            GameGenreServiceUnexistantGenreException
            $e
        ) {
            throw $e;
        } catch (GameGenreInvalidGameIdException $e) {
            throw new GameGenreServiceInvalidGameIdException(
                "Game genre service error: {$e->getMessage()}",
                $e
            );
        } catch (GameGenreInvalidGenreIdException $e) {
            throw new GameGenreServiceInvalidGenreIdException(
                "Game genre service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
        $e) {
            throw $e;
        }
    }

    public function update(int $id, int $gameId, int $genreId): bool
    {
        try {
            $gameGenre = new GameGenre(
                $id,
                $gameId,
                $genreId
            );

            $gameGenre->validateId();
            $gameGenre->validateGameId();
            $gameGenre->validateGenreId();

            $validatedId = $gameGenre->getId();

            $this->gameGenreRepository->checkIfExists($validatedId);

            try {
                $validatedGameId = $gameGenre->getGameId();

                $this->gameRepository->checkIfExists($validatedGameId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new GameGenreServiceUnexistantGameException(
                    "Game genre service error: Game repository: {$e->getMessage()}",
                    $e
                );
            }

            try {
                $validatedGenreId = $gameGenre->getGenreId();

                $this->genreRepository->checkIfExists($validatedGenreId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new GameGenreServiceUnexistantGenreException(
                    "Game genre service error: Genre repository: {$e->getMessage()}",
                    $e
                );
            }

            $wasUpdated = $this->gameGenreRepository->update($gameGenre);

            return $wasUpdated;
        } catch (
            GameGenreServiceUnexistantGameException |
            GameGenreServiceUnexistantGenreException
        $e) {
            throw $e;
        } catch (GameGenreInvalidIdException $e) {
            throw new GameGenreServiceInvalidIdException(
                "Game genre service error: {$e->getMessage()}",
                $e
            );
        } catch (GameGenreInvalidGameIdException $e) {
            throw new GameGenreServiceInvalidGameIdException(
                "Game genre service error: {$e->getMessage()}",
                $e
            );
        } catch (GameGenreInvalidGenreIdException $e) {
            throw new GameGenreServiceInvalidGenreIdException(
                "Game genre service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new GameGenreServiceUnexistantGameGenreException(
                "Game genre service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
        $e) {
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $gameGenre = new GameGenre(
                $id
            );

            $gameGenre->validateId();

            $validatedId = $gameGenre->getId();

            $this->gameGenreRepository->checkIfExists($validatedId);

            $wasDeleted = $this->gameGenreRepository->delete($gameGenre);

            return $wasDeleted;
        } catch (GameGenreInvalidIdException $e) {
            throw new GameGenreServiceInvalidIdException(
                "Game genre service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new GameGenreServiceUnexistantGameGenreException(
                "Game genre service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
        $e) {
            throw $e;
        }
    }

    public function findById(int $id): GameGenre
    {
        try {
            $gameGenre = new GameGenre(
                $id
            );

            $gameGenre->validateId();

            $validatedId = $gameGenre->getId();

            $fetchedGameGenre = $this->gameGenreRepository->findById(
                $validatedId
            );

            return $fetchedGameGenre;
        } catch (GameGenreInvalidIdException $e) {
            throw new GameGenreServiceInvalidIdException(
                "Game genre service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new GameGenreServiceUnexistantGameGenreException(
                "Game genre service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable

        $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            return $this->gameGenreRepository->findAll();
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable

        $e) {
            throw $e;
        }
    }
}
