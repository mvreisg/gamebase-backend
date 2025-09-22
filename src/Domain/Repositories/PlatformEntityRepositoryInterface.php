<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\PlatformEntity;

interface PlatformEntityRepositoryInterface
{
    public function insert(PlatformEntity $platformEntity): PlatformEntity;

    public function update(PlatformEntity $platformEntity): bool;

    public function setIsActive(int $id, bool $isActive): bool;

    public function findById(int $id): PlatformEntity|null;

    public function findAll(): array;

    public function checkDuplicatedNames(string $name): void;
}
