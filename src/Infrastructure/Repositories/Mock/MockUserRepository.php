<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Password;
use Mvreisg\GamebaseBackend\Domain\Data\User;
use Mvreisg\GamebaseBackend\Domain\Data\UserCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Username;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockUserRepository implements UserRepositoryInterface
{
    private UserCollection $collection;
    private Id $id;

    public function __construct()
    {
        $this->collection = new UserCollection();
        $this->id = new Id(0);
    }

    public function insert(User $user): User
    {
        $this->id->increment(1);
        $newUser = new User(
            Id::make($user->getIdValue()),
            Username::make($user->getUsernameValue()),
            Password::make($user->getPasswordValue()),
            $user->getIsActive()
        );
        $this->collection->add($newUser);
        return $newUser;
    }

    public function update(User $user): bool
    {
        $foundUser = $this->collection->findById(
            Id::make($user->getIdValue())
        );

        if ($foundUser === null) {
            throw new MockUnexistantRegisterException(
                "id: {$user->getIdValue()}"
            );
        }

        $hasDifferentUsernames =
            $foundUser->getUsernameValue() !== $user->getUsernameValue();

        $hasDifferentPasswords =
            $foundUser->getPasswordValue() !== $user->getPasswordValue();

        $hasDifferentIsActive =
            $foundUser->getIsActive() !== $user->getIsActive();

        $isDifferent =
            $hasDifferentUsernames ||
            $hasDifferentPasswords ||
            $hasDifferentIsActive;

        if ($isDifferent === false) {
            return false;
        }

        $this->collection->replace(
            Id::make($user->getIdValue()),
            new User(
                Id::make($user->getIdValue()),
                Username::make($user->getUsernameValue()),
                Password::make($user->getPasswordValue()),
                $user->getIsActive()
            )
        );
        return true;
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        $foundUser = $this->collection->findById(
            $id
        );

        if ($foundUser === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        $wasUpdated = $foundUser->getIsActive() !== $isActive;

        if ($wasUpdated === false) {
            return false;
        }

        $this->collection->replace(
            $id,
            new User(
                Id::make($foundUser->getIdValue()),
                Username::make($foundUser->getUsernameValue()),
                Password::make($foundUser->getPasswordValue()),
                $isActive
            )
        );
        return true;
    }

    public function findById(Id $id): User
    {
        $foundUser = $this->collection->findById(
            $id
        );

        if ($foundUser === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }

        return $foundUser;
    }

    public function findByUsername(Username $username): User
    {
        $foundUser = $this->collection->findByUsername(
            $username
        );

        if ($foundUser === null) {
            throw new MockUnexistantRegisterException(
                "username: {$username->getValue()}"
            );
        }

        return $foundUser;
    }

    public function findAll(): UserCollection
    {
        return $this->collection;
    }

    public function checkIfExists(Id $id): void
    {
        $foundUser = $this->collection->findById(
            $id
        );

        if ($foundUser === null) {
            throw new MockUnexistantRegisterException(
                "id: {$id->getValue()}"
            );
        }
    }

    public function checkDuplicatedUsernames(Username $username): void
    {
        $foundUsers = $this->collection->findAllByUsername(
            $username
        );

        if ($foundUsers->count() > 1) {
            throw new MockDuplicatedRegisterException(
                "username: {$username->getValue()}"
            );
        }
    }
}
