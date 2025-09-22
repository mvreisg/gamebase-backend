<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\UserPermissionEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserPermissionEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockUnexistantRegisterException;

class MockUserPermissionEntityRepository implements UserPermissionEntityRepositoryInterface
{
    private array $data;
    private int $id;
    private UserEntityRepositoryInterface $userEntityRepository;
    private PermissionEntityRepositoryInterface $permissionEntityRepository;

    public function __construct(
        UserEntityRepositoryInterface $userEntityRepository,
        PermissionEntityRepositoryInterface $permissionEntityRepository
    ) {
        $this->data = [];
        $this->id = 0;
        $this->userEntityRepository = $userEntityRepository;
        $this->permissionEntityRepository = $permissionEntityRepository;
    }

    public function insert(UserPermissionEntity $userPermissionEntity): UserPermissionEntity
    {
        try {
            $userId = $userPermissionEntity->getUserId();
            $user = $this->userEntityRepository->findById($userId);
            if ($user === null) {
                throw new MockUnexistantRegisterException(
                    $userId
                );
            }

            $permissionId = $userPermissionEntity->getPermissionId();
            $permission = $this->permissionEntityRepository->findById($permissionId);
            if ($permission === null) {
                throw new MockUnexistantRegisterException(
                    $permissionId
                );
            }

            $this->id++;
            $id = $this->id;

            $userPermissionEntity->setId($id);
            $this->data[] = $userPermissionEntity;
            $newUserPermissionEntity = new UserPermissionEntity(
                $id,
                $userId,
                $permissionId
            );
            return $newUserPermissionEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(UserPermissionEntity $userPermissionEntity): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $userPermissionEntity->getId()) {
                $index = $key;
                break;
            }
        }

        if ($index < 0) {
            return false;
        }

        $userPermissionEntityToBeModified = $this->data[$index];

        $hasDifferentUserId =
            $userPermissionEntityToBeModified->getUserId() !== $userPermissionEntity->getUserId();

        $hasDifferentPermissionId =
            $userPermissionEntityToBeModified->getPermissionId() !== $userPermissionEntity->getPermissionId();

        $wasUpdated = $hasDifferentUserId || $hasDifferentPermissionId;

        $this->data[$index] = new UserPermissionEntity(
            $userPermissionEntity->getId(),
            $userPermissionEntity->getUserId(),
            $userPermissionEntity->getPermissionId()
        );

        return $wasUpdated;
    }

    public function delete(UserPermissionEntity $userPermissionEntity): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $userPermissionEntity->getId()) {
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

    public function findById(int $id): UserPermissionEntity|null
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
