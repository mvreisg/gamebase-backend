<?php

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;

interface GamePlatformRepositoryInterface
{
    public function insert(GamePlatform $gamePlatform): GamePlatform;

    public function update(GamePlatform $gamePlatform): bool;

    public function delete(GamePlatform $gamePlatform): bool;

    public function findById(int $id): GamePlatform|null;

    public function findAll(): array;
}
