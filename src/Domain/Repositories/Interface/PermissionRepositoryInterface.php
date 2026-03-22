<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Interface;

use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Entities\Permission;
use Mvreisg\GamebaseBackend\Domain\Entities\PermissionCollection;

interface PermissionRepositoryInterface
{
    public function insert(Permission $permission): Permission;

    public function update(Permission $permission): bool;

    public function findById(Id $id): Permission;

    public function findAll(): PermissionCollection;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function checkIfExists(Id $id): void;

    public function checkDuplicatedNames(Name $name): void;
}
