<?php

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

    public function validateId(mixed $id)
    {
        if ($id === null) {
            throw new EntityInvalidValueException('id é null!');
        }

        if (is_string($id)) {
            throw new EntityInvalidValueException('id é string!');
        }

        if (is_bool($id)) {
            throw new EntityInvalidValueException('id é bool!');
        }

        if ($id <= 0) {
            throw new EntityInvalidValueException('id deve ser maior que 0!');
        }
    }

    public function validateUserName(mixed $userName)
    {
        if ($userName === null) {
            throw new EntityInvalidValueException('username é null!');
        }

        if (is_string($userName) === false) {
            throw new EntityInvalidValueException('username não é string!');
        }

        $userName = trim($userName);

        if ($userName === '') {
            throw new EntityInvalidValueException('username está vazio!');
        }
    }

    public function validatePassWord(mixed $passWord)
    {
        if ($passWord === null) {
            throw new EntityInvalidValueException('password é null!');
        }

        if (is_string($passWord) === false) {
            throw new EntityInvalidValueException('password não é string!');
        }

        $passWord = trim($passWord);

        if ($passWord === '') {
            throw new EntityInvalidValueException('password está vazio!');
        }
    }

    public function validateIsActive(mixed $isActive): void
    {
        if ($isActive === null) {
            throw new EntityInvalidValueException('isActive é null!');
        }

        if (is_string($isActive)) {
            throw new EntityInvalidValueException('isActive é uma string!');
        }

        if (is_numeric($isActive)) {
            throw new EntityInvalidValueException('isActive é numérico!');
        }
    }
}
