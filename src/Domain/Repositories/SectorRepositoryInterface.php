<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Sector;

interface SectorRepositoryInterface
{
    public function insert(Sector $sector): Sector;

    public function update(Sector $sector): bool;

    public function findById(int $id): Sector|null;

    public function findAll(): array;

    public function setIsActive(int $id, bool $isActive): bool;

    public function hasDuplicatedNames(string $name): bool;
}
