<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\User\User;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockUserRepository implements UserRepositoryInterface
{
    private array $data;
    private int $index;

    public function __construct()
    {
        $this->data = [];
        $this->index = 0;
    }

    public function insert(User $user): User
    {
        $this->index++;
        $user->setId($this->index);
        $this->data[] = $user;
        $newUserEntity = new User(
            $user->getId(),
            $user->getUsername(),
            $user->getPassword(),
            $user->getIsActive()
        );
        return $newUserEntity;
    }

    public function update(User $user): bool
    {
        $id = $user->getId();
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $index = $key;
                break;
            }
        }

        if ($index < 0) {
            throw new MockUnexistantRegisterException(
                "id: $id"
            );
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
            throw new MockUnexistantRegisterException(
                "id: $id"
            );
        }

        $foundUserEntity = $this->data[$index];

        $wasUpdated =
            $foundUserEntity->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $wasUpdated;
    }

    public function findById(int $id): User
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }

        throw new MockUnexistantRegisterException(
            "id: $id"
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
            "username: $username"
        );
    }

    public function findAll(): array
    {
        return $this->data;
    }

    public function checkDuplicatedUserNames(string $name): void
    {
        $array = array_filter(
            $this->data,
            fn (User $user) => strcmp($user->getUsername(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedEntryException(
                $name
            );
        }
    }
}
