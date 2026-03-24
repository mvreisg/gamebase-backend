<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Session\Data;

use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermissionCollection;

class SessionData
{
    private Id $userId;
    private Username $username;
    private UserSectorPermissionCollection $userSectorPermissionCollection;

    public function __construct(
        Id $userId,
        Username $username,
        UserSectorPermissionCollection $userSectorPermissionCollection
    ) {
        $this->userId = $userId;
        $this->username = $username;
        $this->userSectorPermissionCollection = $userSectorPermissionCollection;
    }

    public function getUserId(): Id
    {
        return $this->userId;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function getUserSectorPermissionCollection(): UserSectorPermissionCollection
    {
        return $this->userSectorPermissionCollection;
    }
}
