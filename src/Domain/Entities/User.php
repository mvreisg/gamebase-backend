<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;

class User
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
            throw new EntityInvalidValueException('O id deve ser maior que zero!');
        }
    }

    public function validateUserName(): void
    {
        $this->userName = trim($this->userName);

        if ($this->userName === '') {
            throw new EntityInvalidValueException('username está vazio!');
        }
    }

    public function validatePassWord(): void
    {
        $this->passWord = trim($this->passWord);

        if ($this->passWord === '') {
            throw new EntityInvalidValueException('username está vazio!');
        }
    }
}
