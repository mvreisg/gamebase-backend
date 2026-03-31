<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\GamePlatform\Repository;

use Mvreisg\GamebaseBackend\Domain\GamePlatform\Entity\Collection\GamePlatformCollection;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Entity\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

interface GamePlatformRepositoryInterface
{
    public function insert(GamePlatform $gamePlatform): GamePlatform;

    public function update(GamePlatform $gamePlatform): bool;

    public function delete(Id $id): bool;

    public function findById(Id $id): ?GamePlatform;

    public function findAll(): ?GamePlatformCollection;

    public function checkIfExists(Id $id): bool;
}
