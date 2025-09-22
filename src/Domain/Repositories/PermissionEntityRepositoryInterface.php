<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\PermissionEntity;

interface PermissionEntityRepositoryInterface
{
    public function insert(PermissionEntity $permissionEntity): PermissionEntity;

    public function update(PermissionEntity $permissionEntity): bool;

    public function findById(int $id): PermissionEntity|null;

    public function findAll(): array;

    public function setIsActive(int $id, bool $isActive): bool;

    public function checkDuplicatedNames(string $name): void;
}
