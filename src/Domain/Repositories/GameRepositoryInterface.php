<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Game\Game;

interface GameRepositoryInterface
{
    public function insert(Game $game): Game;

    public function update(Game $game): bool;

    public function setIsActive(int $id, bool $isActive): bool;

    public function findById(int $id): Game;

    public function findAll(): array;

    public function checkIfExists(int $id): void;

    public function checkDuplicatedNames(string $name): void;
}
