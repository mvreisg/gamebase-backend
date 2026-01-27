<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Interface;

use Mvreisg\GamebaseBackend\Domain\Data\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Data\GamePlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Id;

interface GamePlatformRepositoryInterface
{
    public function insert(GamePlatform $gamePlatform): GamePlatform;

    public function update(GamePlatform $gamePlatform): bool;

    public function delete(Id $id): bool;

    public function findById(Id $id): GamePlatform;

    public function findAll(): GamePlatformCollection;

    public function checkIfExists(Id $id): void;
}
