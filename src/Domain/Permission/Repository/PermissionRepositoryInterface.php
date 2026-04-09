<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Permission\Repository;

use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Collection\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

interface PermissionRepositoryInterface
{
    public function insert(Permission $permission): Permission;

    public function update(Permission $permission): bool;

    public function findById(Id $id): ?Permission;

    public function findAll(): ?PermissionCollection;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function checkIfExists(Id $id): bool;

    public function checkDuplicatedNames(?Id $id = null, Name $name): bool;
}
