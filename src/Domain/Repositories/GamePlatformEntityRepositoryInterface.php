<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatformEntity;

interface GamePlatformEntityRepositoryInterface
{
    public function insert(GamePlatformEntity $gamePlatformEntity): GamePlatformEntity;

    public function update(GamePlatformEntity $gamePlatformEntity): bool;

    public function delete(GamePlatformEntity $gamePlatformEntity): bool;

    public function findById(int $id): GamePlatformEntity|null;

    public function findAll(): array;
}
