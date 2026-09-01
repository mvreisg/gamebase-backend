<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\Entity;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

class SafeUser
{
    private Id $id;
    private Username $username;
    private bool $isActive;

    public function __construct(
        User $user
    ) {
        $this->id = $user->getId();
        $this->username = $user->getUsername();
        $this->isActive = $user->getIsActive();
    }

    public static function create(
        User $user
    ): self {
        return new self(
            $user
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

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
