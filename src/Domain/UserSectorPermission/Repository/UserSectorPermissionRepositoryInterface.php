<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\Collection\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\UserSectorPermission;

interface UserSectorPermissionRepositoryInterface
{
    public function insert(UserSectorPermission $userSectorPermission): UserSectorPermission;

    public function update(UserSectorPermission $userSectorPermission): bool;

    public function delete(Id $id): bool;

    public function findById(Id $id): ?UserSectorPermission;

    public function findAllByUserId(Id $userId): ?UserSectorPermissionCollection;

    public function findAll(): ?UserSectorPermissionCollection;

    public function checkIfExists(Id $id): bool;
}
