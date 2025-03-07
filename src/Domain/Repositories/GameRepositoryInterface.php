<?php

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Game;

interface GameRepositoryInterface
{
    public function insert(Game $game): Game;

    public function update(Game $game): bool;

    public function setIsActive(int $id, bool $isActive): bool;

    public function findById(int $id): Game|null;

    public function findAll(): array;

    public function hasDuplicatedNames(string $name): bool;
}
