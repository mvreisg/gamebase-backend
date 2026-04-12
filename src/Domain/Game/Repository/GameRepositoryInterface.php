<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Game\Repository;

use Mvreisg\GamebaseBackend\Domain\Game\Entity\Collection\GameCollection;
use Mvreisg\GamebaseBackend\Domain\Game\Entity\Game;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

interface GameRepositoryInterface
{
    public function insert(Game $game): Game;

    public function update(Game $game): bool;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function findById(Id $id): ?Game;

    public function findAll(): ?GameCollection;

    public function checkIfExists(Id $id): bool;

    public function checkDuplicatedNames(?Id $id = null, Name $name): bool;
}
