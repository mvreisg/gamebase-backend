<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Authentication\Data;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

class AuthenticationData
{
    private Id $userId;
    private Username $username;

    public function __construct(
        Id $userId,
        Username $username,
    ) {
        $this->userId = $userId;
        $this->username = $username;
    }

    public function getUserId(): Id
    {
        return $this->userId;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }
}
