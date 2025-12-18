<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\User\User;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedUsernameException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockUserRepository implements UserRepositoryInterface
{
    /**
     * @var User[]
     */
    private array $data;
    private int $idIndex;

    public function __construct()
    {
        $this->data = [];
        $this->idIndex = 0;
    }

    public function insert(User $user): User
    {
        $this->idIndex++;
        $user->setId($this->idIndex);
        $this->data[] = $user;
        return new User(
            $user->getId(),
            $user->getUsername(),
            $user->getPassword(),
            $user->getIsActive()
        );
    }

    public function update(User $user): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $user->getId()) {
                $index = $key;
            }
        }

        if ($index < 0) {
            return false;
        }

        $foundUser = $this->data[$index];

        $hasDifferentUsernames =
            $foundUser->getUsername() !== $user->getUsername();

        $hasDifferentPasswords =
            $foundUser->getPassword() !== $user->getPassword();

        $hasDifferentIsActive =
            $foundUser->getIsActive() !== $user->getIsActive();

        $isDifferent =
            $hasDifferentUsernames ||
            $hasDifferentPasswords ||
            $hasDifferentIsActive;

        if ($isDifferent === false) {
            return false;
        }

        $this->data[$index] = new User(
            $user->getId(),
            $user->getUsername(),
            $user->getPassword(),
            $user->getIsActive()
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

        $foundUser = $this->data[$index];

        $wasUpdated = $foundUser->getIsActive() !== $isActive;

        if ($wasUpdated === false) {
            return false;
        }

        $this->data[$index]->setIsActive($isActive);

        return true;
    }

    public function findById(int $id): User
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant user with id $id"
        );
    }

    public function findByUsername(string $username): User
    {
        foreach ($this->data as $key => $value) {
            if (strcmp($value->getUsername(), $username) === 0) {
                return $value;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant username: $username"
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
            "Unexistant user with id $id"
        );
    }

    public function checkDuplicatedUsernames(string $name): void
    {
        $array = array_filter(
            $this->data,
            fn (User $user) => strcmp($user->getUsername(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedUsernameException(
                "Duplicated username: $name"
            );
        }
    }
}
