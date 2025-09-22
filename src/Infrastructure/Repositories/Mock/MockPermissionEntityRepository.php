<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\PermissionEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockDuplicatedEntryException;

class MockPermissionEntityRepository implements PermissionEntityRepositoryInterface
{
    private array $data;
    private int $index;

    public function __construct()
    {
        $this->data = [];
        $this->index = 0;
    }

    public function insert(PermissionEntity $permissionEntity): PermissionEntity
    {
        $this->index++;
        $permissionEntity->setId($this->index);
        $this->data[] = $permissionEntity;
        $newPermissionEntity = new PermissionEntity(
            $permissionEntity->getId(),
            $permissionEntity->getName(),
            $permissionEntity->getIsActive()
        );
        return $newPermissionEntity;
    }

    public function update(PermissionEntity $permissionEntity): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $permissionEntity->getId()) {
                $index = $key;
            }
        }

        if ($index < 0) {
            return false;
        }

        $this->data[$index] = new PermissionEntity(
            $permissionEntity->getId(),
            $permissionEntity->getName(),
            $permissionEntity->getIsActive()
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

    public function findById(int $id): PermissionEntity|null
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
            fn (PermissionEntity $permissionEntity) => strcmp($permissionEntity->getName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedEntryException(
                $name
            );
        }
    }
}
