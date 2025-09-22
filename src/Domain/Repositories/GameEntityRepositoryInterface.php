<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\GameEntity;

interface GameEntityRepositoryInterface
{
    public function insert(GameEntity $gameEntity): GameEntity;

    public function update(GameEntity $gameEntity): bool;

    public function setIsActive(int $id, bool $isActive): bool;

    public function findById(int $id): GameEntity|null;

    public function findAll(): array;

    public function checkDuplicatedNames(string $name): void;
}
