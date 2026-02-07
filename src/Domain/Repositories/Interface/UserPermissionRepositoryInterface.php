<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Interface;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\UserPermission;
use Mvreisg\GamebaseBackend\Domain\Data\UserPermissionCollection;

interface UserPermissionRepositoryInterface
{
    public function insert(UserPermission $userPermission): UserPermission;

    public function update(UserPermission $userPermission): bool;

    public function delete(Id $id): bool;

    public function findById(Id $id): UserPermission;

    public function findAllByUserId(Id $userId): UserPermissionCollection;

    public function findAll(): UserPermissionCollection;

    public function checkIfExists(Id $id): void;
}
