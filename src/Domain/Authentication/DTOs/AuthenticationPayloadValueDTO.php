<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\DTOs;

class AuthenticationPayloadValueDTO
{
    public int $userId;
    public string $username;
    public array $permissions;
    public array $sectors;

    public function __construct(
        int $userId,
        string $username,
        array $permissions,
        array $sectors
    ) {
        $this->userId = $userId;
        $this->username = $username;
        $this->permissions = $permissions;
        $this->sectors = $sectors;
    }
}
