<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseUnexistantRegisterException;

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
        $id = $user->getId();
        $hasFound = false;
        $index = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $hasFound = true;
                $index = $key;                             
                break;
            }
        }        

        if ($hasFound === false) {
            throw new DatabaseUnexistantRegisterException(
                'O registro com o id ' . $id . ' não existe!'
            );
        }

        $userToBeModified = $this->data[$index];
        
        $userToBeModified->setUserName($user->getUserName());
        $userToBeModified->setPassword($user->getPassWord());
        $userToBeModified->setIsActive($user->getIsActive());

        $this->data[$index] = $userToBeModified;

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
            throw new DatabaseUnexistantRegisterException(
                'O registro com o id ' . $id . ' não existe!'
            );
        }

        $foundUser = $this->data[$index];

        $hasDifference = $foundUser->getIsActive() !== $isActive;

        $foundUser->setIsActive($isActive);

        $this->data[$index] = $foundUser;

        return $hasDifference;
    }

    public function findById(int $id): User
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }

        throw new DatabaseUnexistantRegisterException(
            'O registro com o id ' . $id . ' não existe!'
        );
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

    public function hasDuplicatedUserNames(string $name): bool
    {
        $array = array_filter($this->data, function (User $user) use ($name) {
            return strcmp($user->getUserName(), $name) === 0;
        });
        return count($array) > 0;
    }
}
