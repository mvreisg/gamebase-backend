<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\UserPermission;
use Mvreisg\GamebaseBackend\Domain\Data\UserPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockUserPermissionRepository implements UserPermissionRepositoryInterface
{
    private UserPermissionCollection $collection;
    private Id $id;

    public function __construct()
    {
        $this->collection = new UserPermissionCollection();
        $this->id = Id::make(1);
    }

    public function insert(UserPermission $parameter): UserPermission
    {
        $parameter->setId(
            Id::make(
                $this->id->getValue()
            )
        );
        $this->collection->add(
            $parameter
        );
        $this->id->increment(1);
        return $parameter;
    }

    public function update(UserPermission $userPermission): bool
    {
        $foundUserPermission = $this->collection->findById(
            Id::make($userPermission->getIdValue())
        );

        if ($foundUserPermission === null) {
            throw new MockUnexistantRegisterException(
                "id: {$userPermission->getIdValue()}"
            );
        }

        $hasDifferentUserId =
            $foundUserPermission->getUserIdValue() !== $userPermission->getUserIdValue();

        $hasDifferentPermissionId =
            $foundUserPermission->getPermissionIdValue() !== $userPermission->getPermissionIdValue();

        $isDifferent = $hasDifferentUserId || $hasDifferentPermissionId;

        if ($isDifferent === false) {
            return false;
        }

        $new = new UserPermission(
            Id::make($userPermission->getUserIdValue()),
            Id::make($userPermission->getPermissionIdValue())
        );
        $new->setId(Id::make($userPermission->getIdValue()));

        $this->collection->replace(
            Id::make($userPermission->getIdValue()),
            $new
        );
        return true;
    }

    public function delete(Id $id): bool
    {
        return $this->collection->remove(
            $id
        );
    }

    public function findById(Id $id): UserPermission
    {
        $foundUserPermission = $this->collection->findById(
            $id
        );

        if ($foundUserPermission === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        return $foundUserPermission;
    }

    public function findAllByUserId(Id $userId): UserPermissionCollection
    {
        return $this->collection->findAllByUserId($userId);
    }

    public function findAll(): UserPermissionCollection
    {
        return $this->collection;
    }

    public function checkIfExists(Id $id): void
    {
        $foundUserPermission = $this->collection->findById(
            $id
        );

        if ($foundUserPermission === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }
    }
}
