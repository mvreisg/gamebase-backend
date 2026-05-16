<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\Entity;

use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIdException;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIsActiveException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Exception\NullPasswordException;
use Mvreisg\GamebaseBackend\Domain\User\Exception\NullUsernameException;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Password;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

class User
{
    private ?Id $id;
    private Username $username;
    private Password $password;
    private bool $isActive;

    public function __construct(
        ?Id $id,
        ?Username $username,
        ?Password $password,
        ?bool $isActive
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->isActive = $isActive;
    }

    public static function create(
        ?Id $id,
        ?Username $username,
        ?Password $password,
        ?bool $isActive
    ): self {
        return new self(
            $id,
            $username,
            $password,
            $isActive
        );
    }

    public static function createFromIdOnly(
        Id $id
    ): self {
        return self::create(
            $id,
            null,
            null,
            null
        );
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
        if ($this->username === null) {
            throw new NullUsernameException();
        }
        return $this->username;
    }

    public function getPassword(): Password
    {
        if ($this->password === null) {
            throw new NullPasswordException();
        }
        return $this->password;
    }

    public function getIsActive(): bool
    {
        if ($this->isActive === null) {
            throw new NullIsActiveException(
                User::class
            );
        }
        return $this->isActive;
    }
}
