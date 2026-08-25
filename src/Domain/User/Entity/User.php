<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\Entity;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Password;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

class User
{
    private Id $id;
    private Username $username;
    private Password $password;
    private bool $isActive;

    public function __construct(
        Id $id,
        Username $username,
        Password $password,
        bool $isActive
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->isActive = $isActive;
    }

    public static function create(
        Id $id,
        Username $username,
        Password $password,
        bool $isActive
    ): self {
        return new self(
            $id,
            $username,
            $password,
            $isActive
        );
    }

    public function getId(): Id
    {
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
