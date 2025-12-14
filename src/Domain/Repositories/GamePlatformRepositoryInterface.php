<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform\GamePlatform;

interface GamePlatformRepositoryInterface
{
    public function insert(GamePlatform $gamePlatform): GamePlatform;

    public function update(GamePlatform $gamePlatform): bool;

    public function delete(GamePlatform $gamePlatform): bool;

    public function findById(int $id): GamePlatform;

    public function findAll(): array;

    public function checkIfExists(int $id): void;
}
