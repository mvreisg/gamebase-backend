<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityException;

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
            throw new EntityException(
                "Id is null."
            );
        }
        return $this->id;
    }

    public function getUsername(): Username
    {
        if ($this->username === null) {
            throw new EntityException(
                "Username is null."
            );
        }
        return $this->username;
    }

    public function getPassword(): Password
    {
        if ($this->password === null) {
            throw new EntityException(
                "Password is null."
            );
        }
        return $this->password;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
