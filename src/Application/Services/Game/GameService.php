<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Game;

use Mvreisg\GamebaseBackend\Application\Services\Game\Exceptions\GameServiceDuplicatedNameException;
use Mvreisg\GamebaseBackend\Application\Services\Game\Exceptions\GameServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\Game\Exceptions\GameServiceInvalidNameException;
use Mvreisg\GamebaseBackend\Application\Services\Game\Exceptions\GameServiceUnexistantGameException;
use Mvreisg\GamebaseBackend\Domain\Entities\Game\Exceptions\GameInvalidIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\Game\Exceptions\GameInvalidNameException;
use Mvreisg\GamebaseBackend\Domain\Entities\Game\Game;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryDuplicatedNameException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;

class GameService
{
    private GameRepositoryInterface $repository;

    public function __construct(GameRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(string $name, bool $isActive): Game
    {
        try {
            $game = new Game(
                null,
                $name,
                $isActive
            );

            $game->validateName();

            $validatedName = $game->getName();

            $this->repository->checkDuplicatedNames($validatedName);

            $insertedGame = $this->repository->insert($game);

            return $insertedGame;
        } catch (GameInvalidNameException $e) {
            throw new GameServiceInvalidNameException(
                "Game service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryDuplicatedNameException $e) {
            throw new GameServiceDuplicatedNameException(
                "Game service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function update(int $id, string $name, bool $isActive): bool
    {
        try {
            $game = new Game(
                $id,
                $name,
                $isActive
            );

            $game->validateId();
            $game->validateName();

            $validatedId = $game->getId();

            $this->repository->checkIfExists($validatedId);

            $validatedName = $game->getName();

            $this->repository->checkDuplicatedNames($validatedName);

            $wasUpdated = $this->repository->update($game);

            return $wasUpdated;
        } catch (GameInvalidIdException $e) {
            throw new GameServiceInvalidIdException(
                "Game service error: {$e->getMessage()}",
                $e
            );
        } catch (GameInvalidNameException $e) {
            throw new GameServiceInvalidNameException(
                "Game service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryDuplicatedNameException $e) {
            throw new GameServiceDuplicatedNameException(
                "Game service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new GameServiceUnexistantGameException(
                "Game service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $game = new Game(
                $id,
                null,
                $isActive
            );

            $game->validateId($id);

            $validatedId = $game->getId();

            $this->repository->checkIfExists($validatedId);

            $validatedIsActive = $game->getIsActive();

            $wasUpdated = $this->repository->setIsActive(
                $validatedId,
                $validatedIsActive
            );

            return $wasUpdated;
        } catch (GameInvalidIdException $e) {
            throw new GameServiceInvalidIdException(
                "Game service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new GameServiceUnexistantGameException(
                "Game service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function findById(int $id): Game
    {
        try {
            $game = new Game(
                $id
            );

            $game->validateId();

            $validatedId = $game->getId();

            $foundGame = $this->repository->findById(
                $validatedId
            );

            return $foundGame;
        } catch (GameInvalidIdException $e) {
            throw new GameServiceInvalidIdException(
                "Game service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new GameServiceUnexistantGameException(
                "Game service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            return $this->repository->findAll();
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }
}
