<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockUserPermissionRepository implements UserPermissionRepositoryInterface
{
    private array $data;
    private int $id;
    private UserRepositoryInterface $userRepository;
    private PermissionRepositoryInterface $permissionEntityRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PermissionRepositoryInterface $permissionEntityRepository
    ) {
        $this->data = [];
        $this->id = 0;
        $this->userRepository = $userRepository;
        $this->permissionEntityRepository = $permissionEntityRepository;
    }

    public function insert(UserPermission $userPermission): UserPermission
    {
        try {
            $userId = $userPermission->getUserId();
            $user = $this->userRepository->findById($userId);
            if ($user === null) {
                throw new MockUnexistantRegisterException(
                    "userId: $userId"
                );
            }

            $permissionId = $userPermission->getPermissionId();
            $permission = $this->permissionEntityRepository->findById($permissionId);
            if ($permission === null) {
                throw new MockUnexistantRegisterException(
                    "permissionId: $permissionId"
                );
            }

            $this->id++;
            $id = $this->id;

            $userPermission->setId($id);
            $this->data[] = $userPermission;
            $newUserPermissionEntity = new UserPermission(
                $id,
                $userId,
                $permissionId
            );
            return $newUserPermissionEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(UserPermission $userPermission): bool
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

        $userPermissionEntityToBeModified = $this->data[$index];

        $hasDifferentUserId =
            $userPermissionEntityToBeModified->getUserId() !== $userPermission->getUserId();

        $hasDifferentPermissionId =
            $userPermissionEntityToBeModified->getPermissionId() !== $userPermission->getPermissionId();

        $wasUpdated = $hasDifferentUserId || $hasDifferentPermissionId;

        $this->data[$index] = new UserPermission(
            $userPermission->getId(),
            $userPermission->getUserId(),
            $userPermission->getPermissionId()
        );

        return $wasUpdated;
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

    /*
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

        $findedUserPermissionEntity = $this->data[$idToSet];

        $changedSomething = $findedUserPermissionEntity->getIsActive() !== $isActive;

        if ($changedSomething) {
            $this->data[$idToSet]->setIsActive($isActive);
            return true;
        }

        return false;
    }
    */

    public function findById(int $id): UserPermission
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
