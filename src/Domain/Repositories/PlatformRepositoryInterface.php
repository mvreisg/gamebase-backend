<?php

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Platform;

interface PlatformRepositoryInterface
{
    public function insert(Platform $platform): Platform;

    public function update(Platform $platform): bool;

    public function setIsActive(int $id, bool $isActive): bool;

    public function findById(int $id): Platform|null;

    public function findAll(): array;

    public function hasDuplicatedNames(string $name): bool;
}
