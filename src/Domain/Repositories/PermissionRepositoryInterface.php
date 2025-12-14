<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Permission\Permission;

interface PermissionRepositoryInterface
{
    public function insert(Permission $permission): Permission;

    public function update(Permission $permission): bool;

    public function findById(int $id): Permission;

    public function findAll(): array;

    public function setIsActive(int $id, bool $isActive): bool;

    public function checkIfExists(int $id): void;

    public function checkDuplicatedNames(string $name): void;
}
