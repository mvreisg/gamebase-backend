<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Interface;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermission;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermissionCollection;

interface SectorPermissionRepositoryInterface
{
    public function insert(SectorPermission $sectorPermission): SectorPermission;

    public function update(SectorPermission $sectorPermission): bool;

    public function delete(Id $id): bool;

    public function findById(Id $id): SectorPermission;

    public function findAllByPermissionId(Id $permissionId): SectorPermissionCollection;

    public function findAll(): SectorPermissionCollection;

    public function checkIfExists(Id $id): void;
}
