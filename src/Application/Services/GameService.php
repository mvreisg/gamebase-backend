<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\GameEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameEntityRepositoryInterface;

class GameService
{
    private GameEntityRepositoryInterface $repository;

    public function __construct(GameEntityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(string $name, bool $isActive): GameEntity
    {
        $gameEntity = new GameEntity(
            PHP_INT_MAX,
            $name,
            $isActive
        );

        try {
            $gameEntity->validateName();

            $validatedName = $gameEntity->getName();

            $this->repository->checkDuplicatedNames($validatedName);

            $insertedGameEntity = $this->repository->insert($gameEntity);

            return $insertedGameEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(int $id, string $name, bool $isActive): bool
    {
        $gameEntity = new GameEntity(
            $id,
            $name,
            $isActive
        );

        try {
            $gameEntity->validateId();
            $gameEntity->validateName();

            /*
            $validatedName = $game->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException('O nome do jogo a ser atualizado já existe no repositório!');
            }
            */

            $wasUpdated = $this->repository->update($gameEntity);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $gameEntity = new GameEntity(
            $id,
            '',
            $isActive
        );

        try {
            $gameEntity->validateId($id);

            $validatedId = $gameEntity->getId();
            $validatedIsActive = $gameEntity->getIsActive();

            $wasUpdated = $this->repository->setIsActive(
                $validatedId,
                $validatedIsActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): GameEntity|null
    {
        $gameEntity = new GameEntity(
            $id
        );

        try {
            $gameEntity->validateId();

            $validatedId = $gameEntity->getId();

            $fetchedGameEntity = $this->repository->findById(
                $validatedId
            );

            return $fetchedGameEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $fetchedGameEntities = $this->repository->findAll();

            return $fetchedGameEntities;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
