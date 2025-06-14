<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Permission;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;

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
        $newPermission = new Permission();
        $newPermission->setId($permission->getId());
        $newPermission->setName($permission->getName());
        $newPermission->setIsActive($permission->getIsActive());
        return $newPermission;
    }

    public function update(Permission $permission): bool
    {
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $permission->getId()) {
                $index = $key;
            }
        }

        if ($index === null) {
            return false;
        }

        $modifiedPermission = $this->data[$index];

        $modifiedPermission->setId($permission->getId());
        $modifiedPermission->setName($permission->getName());
        $modifiedPermission->setIsActive($permission->getIsActive());

        $this->data[$index] = $modifiedPermission;

        return true;
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $index = $key;
            }
        }

        if ($index === null) {
            return false;
        }

        $foundPermission = $this->data[$index];

        $changedSomething = $foundPermission->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $changedSomething;
    }

    public function findById(int $id): Permission|null
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

    public function hasDuplicatedNames(string $name): bool
    {
        $array = array_filter($this->data, function (Permission $permission) use ($name) {
            return strcmp($permission->getName(), $name) === 0;
        });
        return count($array) > 0;
    }
}
