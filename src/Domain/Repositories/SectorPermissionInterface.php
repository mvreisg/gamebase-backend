<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\SectorPermission\SectorPermission;

interface SectorPermissionInterface
{
    public function insert(SectorPermission $sectorPermission): SectorPermission;

    public function update(SectorPermission $sectorPermission): bool;

    public function delete(SectorPermission $sectorPermission): bool;

    public function findById(int $id): SectorPermission;

    public function findAll(): array;

    public function checkIfExists(int $id): void;
}
