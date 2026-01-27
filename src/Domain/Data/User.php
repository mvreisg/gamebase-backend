<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class User
{
    private ?Id $id;
    private ?Username $username;
    private ?Password $password;
    private bool $isActive;

    public function __construct(?Id $id = null, ?Username $username = null, ?Password $password = null, bool $isActive = false)
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->isActive = $isActive;
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

    public function alterPasswordValue(string $value): void
    {
        if ($this->password === null) {
            throw new \InvalidArgumentException(
                "Password is null."
            );
        }
        $this->password->alterValue($value);
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
