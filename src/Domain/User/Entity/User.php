<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\Entity;

use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIdException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Password;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

class User
{
    private ?Id $id;
    private Username $username;
    private Password $password;
    private bool $isActive;

    public function __construct(Username $username, Password $password, bool $isActive)
    {
        $this->id = null;
        $this->username = $username;
        $this->password = $password;
        $this->isActive = $isActive;
    }

    public function setId(Id $id): void
    {
        $this->id = $id;
    }

    public function getId(): Id
    {
        if ($this->id === null) {
            throw new NullIdException(
                User::class
            );
        }
        return $this->id;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
