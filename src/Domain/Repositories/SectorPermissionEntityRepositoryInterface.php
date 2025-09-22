<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\SectorPermissionEntity;

interface SectorPermissionEntityRepositoryInterface
{
    public function insert(SectorPermissionEntity $sectorPermissionEntity): SectorPermissionEntity;

    public function update(SectorPermissionEntity $sectorPermissionEntity): bool;

    public function delete(SectorPermissionEntity $sectorPermissionEntity): bool;

    public function findById(int $id): SectorPermissionEntity|null;

    public function findAll(): array;
}
