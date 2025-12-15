<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Permission\Permission;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedNameException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockPermissionRepository implements PermissionRepositoryInterface
{
    /**
     * @var Permission[]
     */
    private array $data;
    private int $idIndex;

    public function __construct()
    {
        $this->data = [];
        $this->idIndex = 0;
    }

    public function insert(Permission $permission): Permission
    {
        $this->idIndex++;
        $permission->setId($this->idIndex);
        $this->data[] = $permission;
        return new Permission(
            $permission->getId(),
            $permission->getName(),
            $permission->getIsActive()
        );
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

        $foundPermission = $this->data[$index];

        $hasDifferentNames =
            $foundPermission->getName() !== $permission->getName();

        $hasDifferentIsActive =
            $foundPermission->getIsActive() !== $permission->getIsActive();

        $isDifferent = $hasDifferentNames || $hasDifferentIsActive;

        if ($isDifferent === false) {
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

        $foundPermission = $this->data[$index];

        $wasUpdated = $foundPermission->getIsActive() !== $isActive;

        if ($wasUpdated === false) {
            return false;
        }

        $this->data[$index]->setIsActive($isActive);

        return true;
    }

    public function findById(int $id): Permission
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant repository with id $id"
        );
    }

    public function findAll(): array
    {
        return $this->data;
    }

    public function checkIfExists(int $id): void
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant permission with id $id"
        );
    }

    public function checkDuplicatedNames(string $name): void
    {
        $array = array_filter(
            $this->data,
            fn (Permission $permission) => strcmp($permission->getName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedNameException(
                "Duplicated permission name: $name"
            );
        }
    }
}
