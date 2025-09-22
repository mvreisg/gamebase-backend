<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\UserEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockUnexistantRegisterException;

class MockUserEntityRepository implements UserEntityRepositoryInterface
{
    private array $data;
    private int $index;

    public function __construct()
    {
        $this->data = [];
        $this->index = 0;
    }

    public function insert(UserEntity $userEntity): UserEntity
    {
        $this->index++;
        $userEntity->setId($this->index);
        $this->data[] = $userEntity;
        $newUserEntity = new UserEntity(
            $userEntity->getId(),
            $userEntity->getUserName(),
            $userEntity->getPassWord(),
            $userEntity->getIsActive()
        );
        return $newUserEntity;
    }

    public function update(UserEntity $userEntity): bool
    {
        $id = $userEntity->getId();
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $index = $key;
                break;
            }
        }

        if ($index < 0) {
            throw new MockUnexistantRegisterException(
                $id
            );
        }

        $this->data[$index] = new UserEntity(
            $userEntity->getId(),
            $userEntity->getUserName(),
            $userEntity->getPassWord(),
            $userEntity->getIsActive()
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
                $id
            );
        }

        $foundUserEntity = $this->data[$index];

        $wasUpdated =
            $foundUserEntity->getIsActive() !== $isActive;

        $this->data[$index]->setIsActive($isActive);

        return $wasUpdated;
    }

    public function findById(int $id): UserEntity
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }

        throw new MockUnexistantRegisterException(
            $id
        );
    }

    public function findByUserName(string $userName): UserEntity
    {
        foreach ($this->data as $key => $value) {
            if (strcmp($value->getUserName(), $userName) === 0) {
                return $value;
            }
        }

        throw new MockUnexistantRegisterException(
            $userName
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
            fn (UserEntity $userEntity) => strcmp($userEntity->getUserName(), $name) === 0
        );
        if (count($array) > 0) {
            throw new MockDuplicatedEntryException(
                $name
            );
        }
    }
}
