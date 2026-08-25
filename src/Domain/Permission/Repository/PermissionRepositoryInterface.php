<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Permission\Repository;

use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Collection\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Permission\Repository\Dto\PermissionRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\Permission\Repository\Dto\PermissionRepositoryInterfaceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

interface PermissionRepositoryInterface
{
    public function insert(PermissionRepositoryInterfaceInsertDto $dto): Permission;

    public function update(PermissionRepositoryInterfaceUpdateDto $dto): bool;

    public function findById(Id $id): ?Permission;

    public function findAll(): ?PermissionCollection;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function checkIfExists(Id $id): bool;

    public function checkIfNameExists(Name $name): ?Id;

    public function checkIfValueExists(PermissionValue $value): ?Id;
}
