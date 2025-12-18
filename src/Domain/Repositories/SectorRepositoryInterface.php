<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Sector\Sector;

interface SectorRepositoryInterface
{
    public function insert(Sector $sector): Sector;

    public function update(Sector $sector): bool;

    public function findById(int $id): Sector;

    public function findAll(): array;

    public function setIsActive(int $id, bool $isActive): bool;

    public function checkIfExists(int $id): void;

    public function checkDuplicatedNames(string $name): void;
}
