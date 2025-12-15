<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission\UserPermission;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockUserPermissionRepository implements UserPermissionRepositoryInterface
{
    private array $data;
    private int $idIndex;

    public function __construct()
    {
        $this->data = [];
        $this->idIndex = 0;
    }

    public function insert(UserPermission $userPermission): UserPermission
    {
        try {
            $this->idIndex++;
            $userPermission->setId($this->idIndex);
            $this->data[] = $userPermission;
            return new UserPermission(
                $userPermission->getId(),
                $userPermission->getUserId(),
                $userPermission->getPermissionId()
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(UserPermission $userPermission): bool
    {
        try {
            $index = -1;
            foreach ($this->data as $key => $value) {
                if ($value->getId() === $userPermission->getId()) {
                    $index = $key;
                    break;
                }
            }

            if ($index < 0) {
                return false;
            }

            $foundUserPermission = $this->data[$index];

            $hasDifferentUserId =
                $foundUserPermission->getUserId() !== $userPermission->getUserId();

            $hasDifferentPermissionId =
                $foundUserPermission->getPermissionId() !== $userPermission->getPermissionId();

            $isDifferent = $hasDifferentUserId || $hasDifferentPermissionId;

            if ($isDifferent === false) {
                return false;
            }

            $this->data[$index] = new UserPermission(
                $userPermission->getId(),
                $userPermission->getUserId(),
                $userPermission->getPermissionId()
            );

            return true;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(UserPermission $userPermission): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $userPermission->getId()) {
                $index = $key;
                break;
            }
        }

        if ($index < 0) {
            return false;
        }

        unset($this->data[$index]);
        return true;
    }

    public function findById(int $id): UserPermission
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant user permission with id $id"
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
            "Unexistant user permission with id $id"
        );
    }
}
