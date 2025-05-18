<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Permission;

interface PermissionRepositoryInterface
{
    public function insert(Permission $permission): Permission;

    public function update(Permission $permission): bool;

    public function findById(int $id): Permission|null;

    public function findAll(): array;

    public function setIsActive(int $id, bool $isActive): bool;

    public function hasDuplicatedNames(string $name): bool;
}
