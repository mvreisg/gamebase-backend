<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\UserPermissionEntity;

interface UserPermissionEntityRepositoryInterface
{
    public function insert(UserPermissionEntity $userPermissionEntity): UserPermissionEntity;

    public function update(UserPermissionEntity $userPermissionEntity): bool;

    public function delete(UserPermissionEntity $userPermissionEntity): bool;

    public function findById(int $id): UserPermissionEntity|null;

    public function findAll(): array;
}
