<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\Entities\EntityInvalidValueException;

class UserEntity
{
    private int $id;
    private string $userName;
    private string $passWord;
    private bool $isActive;

    public function __construct(int $id = 0, string $userName = '', string $passWord = '', bool $isActive = false)
    {
        $this->id = $id;
        $this->userName = $userName;
        $this->passWord = $passWord;
        $this->isActive = $isActive;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }

    public function getPassWord(): string
    {
        return $this->passWord;
    }

    public function setPassword(string $passWord): void
    {
        $this->passWord = $passWord;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function validateId(): void
    {
        if ($this->id <= 0) {
            throw new EntityInvalidValueException(
                'The id must be greater than zero!'
            );
        }
    }

    public function validateUserName(): void
    {
        $this->userName = trim($this->userName);

        if ($this->userName === '') {
            throw new EntityInvalidValueException(
                'The username is empty!'
            );
        }
    }

    public function validatePassWord(): void
    {
        $this->passWord = trim($this->passWord);

        if ($this->passWord === '') {
            throw new EntityInvalidValueException(
                'The password is empty!'
            );
        }
    }
}
