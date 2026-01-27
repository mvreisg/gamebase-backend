<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Data\Permission;
use Mvreisg\GamebaseBackend\Domain\Data\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockPermissionRepository implements PermissionRepositoryInterface
{
    private PermissionCollection $collection;
    private Id $id;

    public function __construct()
    {
        $this->collection = new PermissionCollection(null);
        $this->id = new Id(0);
    }

    public function insert(Permission $permission): Permission
    {
        $this->id->increment(1);
        $permission = new Permission(
            new Id($this->id->getValue()),
            new Name($permission->getNameValue()),
            $permission->getIsActive()
        );
        $this->collection->add($permission);
        return $permission;
    }

    public function update(Permission $permission): bool
    {
        $foundPermission = $this->collection->findById(
            Id::make($permission->getIdValue())
        );

        if ($foundPermission === null) {
            throw new MockUnexistantRegisterException(
                "id: {$permission->getIdValue()}"
            );
        }

        $hasDifferentNames =
            $foundPermission->getNameValue() !== $permission->getNameValue();

        $hasDifferentIsActive =
            $foundPermission->getIsActive() !== $permission->getIsActive();

        $isDifferent = $hasDifferentNames || $hasDifferentIsActive;

        if ($isDifferent === false) {
            return false;
        }

        $this->collection->replace(
            Id::make($permission->getIdValue()),
            new Permission(
                Id::make($permission->getIdValue()),
                Name::make($permission->getNameValue()),
                $permission->getIsActive()
            )
        );
        return true;
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        $foundPermission = $this->collection->findById(
            $id
        );

        if ($foundPermission === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        $wasUpdated = $foundPermission->getIsActive() !== $isActive;

        if ($wasUpdated === false) {
            return false;
        }
        $this->collection->replace(
            $id,
            new Permission(
                Id::make($foundPermission->getIdValue()),
                Name::make($foundPermission->getNameValue()),
                $isActive
            )
        );
        return true;
    }

    public function findById(Id $id): Permission
    {
        $foundPermission = $this->collection->findById(
            $id
        );

        if ($foundPermission === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        return $foundPermission;
    }

    public function findAll(): PermissionCollection
    {
        return $this->collection;
    }

    public function checkIfExists(Id $id): void
    {
        $foundPermission = $this->collection->findById(
            $id
        );

        if ($foundPermission === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }
    }

    public function checkDuplicatedNames(Name $name): void
    {
        $foundPermissions = $this->collection->findByName(
            $name
        );

        if ($foundPermissions->count() > 1) {
            throw new MockDuplicatedRegisterException(
                "name: {$name->getValue()}"
            );
        }
    }
}
