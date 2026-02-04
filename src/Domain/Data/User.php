<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

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

    public function getIdValue(): int
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException(
                "Id is null."
            );
        }
        return $this->id->getValue();
    }

    public function getUsernameValue(): string
    {
        if ($this->username === null) {
            throw new \InvalidArgumentException(
                "Username is null."
            );
        }
        return $this->username->getValue();
    }

    public function getPasswordValue(): string
    {
        if ($this->password === null) {
            throw new \InvalidArgumentException(
                "Password is null."
            );
        }
        return $this->password->getValue();
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
