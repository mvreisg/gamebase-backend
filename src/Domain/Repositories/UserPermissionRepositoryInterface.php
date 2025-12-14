<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission\UserPermission;

interface UserPermissionRepositoryInterface
{
    public function insert(UserPermission $userPermission): UserPermission;

    public function update(UserPermission $userPermission): bool;

    public function delete(UserPermission $userPermission): bool;

    public function findById(int $id): UserPermission;

    public function findAll(): array;

    public function checkIfExists(int $id): void;
}
