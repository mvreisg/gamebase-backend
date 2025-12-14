<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Permission;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedEntryException;

class MockPermissionRepository implements PermissionRepositoryInterface
{
    private array $data;
    private int $index;

    public function __construct()
    {
        $this->data = [];
        $this->index = 0;
    }

    public function insert(Permission $permission): Permission
    {
        $this->index++;
        $permission->setId($this->index);
        $this->data[] = $permission;
        $newPermissionEntity = new Permission(
            $permission->getId(),
            $permission->getName(),
            $permission->getIsActive()
        );
        return $newPermissionEntity;
    }

    public function update(Permission $permission): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $permission->getId()) {
                $index = $key;
            }
        }

        if ($index < 0) {
            return false;
        }

        $this->data[$index] = new Permission(
            $permission->getId(),
            $permission->getName(),
            $permission->getIsActive()
        );

        return true;
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $index = $key;
            }
        }

        if ($index < 0) {
            return false;
        }

        $foundPermissionEntity = $this->data[$index];

        $wasUpdated =
            $foundPermissionEntity->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $wasUpdated;
    }

    public function findById(int $id): Permission
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        return null;
    }

    public function findAll(): array
    {
        return $this->data;
    }

    public function checkDuplicatedNames(string $name): void
    {
        $array = array_filter(
            $this->data,
            fn (Permission $permission) => strcmp($permission->getName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedEntryException(
                $name
            );
        }
    }
}
