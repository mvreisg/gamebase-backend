<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockUserSectorPermissionRepository implements UserSectorPermissionRepositoryInterface
{
    private UserSectorPermissionCollection $collection;
    private Id $id;

    public function __construct()
    {
        $this->collection = new UserSectorPermissionCollection(null);
        $this->id = Id::make(1);
    }

    public function insert(UserSectorPermission $parameter): UserSectorPermission
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

    public function update(UserSectorPermission $userSectorPermission): bool
    {
        $foundUserPermission = $this->collection->findById(
            Id::make($userSectorPermission->getIdValue())
        );

        if ($foundUserPermission === null) {
            throw new MockUnexistantRegisterException(
                "id: {$userSectorPermission->getIdValue()}"
            );
        }

        $hasDifferentUserId =
            $foundUserPermission->getUserIdValue() !== $userSectorPermission->getUserIdValue();

        $hasDifferentSectorId =
            $foundUserPermission->getSectorIdValue() !== $userSectorPermission->getSectorIdValue();

        $hasDifferentPermissionId =
            $foundUserPermission->getPermissionIdValue() !== $userSectorPermission->getPermissionIdValue();

        $isDifferent = $hasDifferentUserId || $hasDifferentPermissionId;

        if ($isDifferent === false) {
            return false;
        }

        $new = new UserSectorPermission(
            Id::make($userSectorPermission->getUserIdValue()),
            Id::make($userSectorPermission->getSectorIdValue()),
            Id::make($userSectorPermission->getPermissionIdValue())
        );
        $new->setId(Id::make($userSectorPermission->getIdValue()));

        $this->collection->replace(
            Id::make($userSectorPermission->getIdValue()),
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

    public function findById(Id $id): UserSectorPermission
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

    public function findAllByUserId(Id $userId): UserSectorPermissionCollection
    {
        return $this->collection->findAllByUserId($userId);
    }

    public function findAll(): UserSectorPermissionCollection
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
