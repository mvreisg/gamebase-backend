<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Interface;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\Data\UserSectorPermissionCollection;

interface UserSectorPermissionRepositoryInterface
{
    public function insert(UserSectorPermission $userSectorPermission): UserSectorPermission;

    public function update(UserSectorPermission $userSectorPermission): bool;

    public function delete(Id $id): bool;

    public function findById(Id $id): UserSectorPermission;

    public function findAllByUserId(Id $userId): UserSectorPermissionCollection;

    public function findAll(): UserSectorPermissionCollection;

    public function checkIfExists(Id $id): void;
}
