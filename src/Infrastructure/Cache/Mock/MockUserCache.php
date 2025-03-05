<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock;

use Mvreisg\GamebaseBackend\Domain\Cache\UserCacheInterface;

class MockUserCache implements UserCacheInterface
{
    private array $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function set(string $userName, mixed $token): void
    {
        $this->data[$userName] = $token;
    }

    public function get(string $userName): string|null
    {
        return $this->data[$userName];
    }
}
