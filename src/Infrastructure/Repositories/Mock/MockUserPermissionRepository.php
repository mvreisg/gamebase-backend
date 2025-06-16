<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserPermissionRepositoryInterface;

class MockUserPermissionRepository implements UserPermissionRepositoryInterface
{
    private array $data;
    private int $id;

    public function __construct()
    {
        $this->data = [];
        $this->id = 0;
    }

    public function insert(UserPermission $userPermission): UserPermission
    {
        $this->id++;
        $userPermission->setId($this->id);
        $this->data[] = $userPermission;
        $newUserPermission = new UserPermission(
            $userPermission->getId(),
            $userPermission->getUserId(),
            $userPermission->getPermissionId()
        );
        return $newUserPermission;
    }

    public function update(UserPermission $userPermission): bool
    {
        $idToUpdate = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $userPermission->getId()) {
                $idToUpdate = $key;
            }
        }

        if ($idToUpdate === null) {
            return false;
        }

        $modifiedUserPermission = $this->data[$idToUpdate];

        $modifiedUserPermission->setId($userPermission->getId());
        $modifiedUserPermission->setUserId($userPermission->getUserId());
        $modifiedUserPermission->setPermissionId($userPermission->getPermissionId());

        $this->data[$idToUpdate] = $modifiedUserPermission;

        return true;
    }

    public function delete(UserPermission $userPermission): bool
    {
        $idToDelete = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $userPermission->getId()) {
                $idToDelete = $key;
                break;
            }
        }

        if ($idToDelete > -1) {
            unset($this->data[$idToDelete]);
            return true;
        } else {
            return false;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $idToSet = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $idToSet = $key;
            }
        }

        if ($idToSet === null) {
            return false;
        }

        $findedUserPermission = $this->data[$idToSet];

        $changedSomething = $findedUserPermission->getIsActive() !== $isActive;

        if ($changedSomething) {
            $this->data[$idToSet]->setIsActive($isActive);
            return true;
        }        
        
        return false;
    }

    public function findById(int $id): UserPermission|null
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
}
