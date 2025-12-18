<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\DTOs;

class AuthenticationPayloadValueDTO
{
    public string $username;
    public array $permissions;
    public array $sectors;

    public function __construct(string $username, array $permissions, array $sectors)
    {
        $this->username = $username;
        $this->permissions = $permissions;
        $this->sectors = $sectors;
    }
}
