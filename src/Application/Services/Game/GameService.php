<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Game;

use Mvreisg\GamebaseBackend\Domain\Entities\Game;
use Mvreisg\GamebaseBackend\Domain\Entities\GameCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GameRepositoryInterface;

class GameService
{
    private GameRepositoryInterface $repository;

    public function __construct(GameRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(Game $game): Game
    {
        try {
            $this->repository->checkDuplicatedNames(
                Name::make($game->getName()->getValue())
            );

            $insertedGame = $this->repository->insert($game);

            return $insertedGame;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(Game $game): bool
    {
        try {
            $this->repository->checkIfExists(
                $game->getId()
            );

            $this->repository->checkDuplicatedNames(
                $game->getName()
            );

            $wasUpdated = $this->repository->update($game);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        try {
            $this->repository->checkIfExists($id);

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): Game
    {
        try {
            $foundGame = $this->repository->findById(
                $id
            );

            return $foundGame;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): GameCollection
    {
        try {
            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
