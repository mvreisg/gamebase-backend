<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities\User;

use Mvreisg\GamebaseBackend\Domain\Entities\User\Exceptions\UserInvalidIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\User\Exceptions\UserInvalidPasswordException;
use Mvreisg\GamebaseBackend\Domain\Entities\User\Exceptions\UserInvalidUsernameException;

class User
{
    private ?int $id;
    private ?string $username;
    private ?string $password;
    private bool $isActive;

    public function __construct(?int $id = null, ?string $username = "", ?string $password = "", bool $isActive = false)
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->isActive = $isActive;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
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
        if ($this->getId() <= 0) {
            throw new UserInvalidIdException(
                "The id must be greater than zero!"
            );
        }
    }

    public function validateUsername(): void
    {
        $originalUsername = trim($this->getUsername());

        if ($originalUsername === "") {
            throw new UserInvalidUsernameException(
                "The username is empty!"
            );
        }

        $isInvalid = preg_match("/[^a-zA-Z0-9]/", $originalUsername);
        if ($isInvalid) {
            throw new UserInvalidUsernameException(
                "The username is invalid!"
            );
        }

        $this->setUsername($originalUsername);
    }

    public function validatePassword(): void
    {
        $originalPassword = trim($this->getPassword());

        if ($originalPassword === "") {
            throw new UserInvalidPasswordException(
                "The password is empty!"
            );
        }

        $isInvalid = preg_match("/[^a-zA-Z0-9]/", $originalPassword);
        if ($isInvalid) {
            throw new UserInvalidPasswordException(
                "The password is invalid!"
            );
        }

        $this->setPassword($originalPassword);
    }
}
