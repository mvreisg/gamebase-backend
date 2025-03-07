<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;

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
        $newUser = new User();
        $newUser->setId($user->getId());
        $newUser->setUserName($user->getUserName());
        $newUser->setPassword($user->getPassWord());
        $newUser->setIsActive($user->getIsActive());
        return $newUser;
    }

    public function update(User $user): bool
    {
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $user->getId()) {
                $index = $key;
            }
        }

        if ($index === null) {
            return false;
        }

        $modifiedUser = $this->data[$index];

        $modifiedUser->setId($user->getId());
        $modifiedUser->setUserName($user->getUserName());
        $modifiedUser->setPassword($user->getPassWord());
        $modifiedUser->setIsActive($user->getIsActive());

        $this->data[$index] = $modifiedUser;

        return true;
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $index = $key;
            }
        }

        if ($index === null) {
            return false;
        }

        $findedUser = $this->data[$index];

        $changedSomething = $findedUser->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $changedSomething;
    }

    public function findById(int $id): User|null
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        return null;
    }

    public function findByUserName(string $userName): User|null
    {
        foreach ($this->data as $key => $value) {
            if (strcmp($value->getUserName(), $userName) === 0) {
                return $value;
            }
        }
        return null;
    }

    public function findAll(): array
    {
        return $this->data;
    }

    public function hasDuplicatedUserName(string $name): bool
    {
        $array = array_filter($this->data, function (User $user) use ($name) {
            return strcmp($user->getUserName(), $name) === 0;
        });
        return count($array) > 0;
    }
}
