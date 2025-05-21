<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\Game;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;

class GameService
{
    private GameRepositoryInterface $repository;

    public function __construct(GameRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(string $name, bool $isActive): Game
    {
        $game = new Game();

        try {
            $game->setName($name);
            $game->setIsActive($isActive);

            $game->validateName();

            $validatedName = $game->getName();

            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome do jogo a ser inserido já existe no repositório!'
                );
            }

            $game = $this->repository->insert($game);

            return $game;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            DatabaseDuplicatedEntryException |
            PDOException |
            EntityInvalidValueException $e
        ) {
            throw $e;
        }
    }

    public function update(int $id, string $name, bool $isActive): bool
    {
        $game = new Game();

        try {
            $game->setId($id);
            $game->setName($name);
            $game->setIsActive($isActive);

            $game->validateId();
            $game->validateName();

            /*
            $validatedName = $game->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException('O nome do jogo a ser atualizado já existe no repositório!');
            }
            */
            $wasSomeUpdateHappened = $this->repository->update($game);

            return $wasSomeUpdateHappened;
        } catch (
            EntityInvalidValueException |
            DatabaseDuplicatedEntryException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $game = new Game();

        try {
            $game->setId($id);
            $game->setIsActive($isActive);

            $game->validateId($id);

            $wasTheUpdateSuccessful = $this->repository->setIsActive($id, $isActive);

            return $wasTheUpdateSuccessful;
        } catch (
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function findById(int $id): Game|null
    {
        $game = new Game();

        try {
            $game->setId($id);

            $game->validateId();

            $game = $this->repository->findById($id);

            return $game;
        } catch (
            EntityInvalidValueException |
            DatabaseFetchFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $games = $this->repository->findAll();

            return $games;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }
}
