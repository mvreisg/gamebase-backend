<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Interface;

use Mvreisg\GamebaseBackend\Domain\Entities\Game;
use Mvreisg\GamebaseBackend\Domain\Entities\GameCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;

interface GameRepositoryInterface
{
    public function insert(Game $game): Game;

    public function update(Game $game): bool;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function findById(Id $id): Game;

    public function findAll(): GameCollection;

    public function checkIfExists(Id $id): void;

    public function checkDuplicatedNames(Name $name): void;
}
