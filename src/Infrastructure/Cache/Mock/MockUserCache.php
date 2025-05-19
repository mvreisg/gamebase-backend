<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock;

use DateTimeImmutable;
use Mvreisg\GamebaseBackend\Domain\Cache\UserCacheInterface;

class MockUserCache implements UserCacheInterface
{
    private array $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function set(string $key, mixed $token): void
    {
        $this->data[$key]['token'] = $token;
    }

    public function get(string $key): string|null
    {
        if ($this->exists($key)) {
            return $this->data[$key];
        }
        return null;
    }

    public function exists(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function delete(string $key): bool
    {
        if ($this->exists($key)){
            unset($this->data[$key]);
            return true;
        }
        return false;
    }

    public function expire(string $key, int $seconds): void
    {
        if ($this->exists($key)){            
            $this->data[$key]['expiresOn'] = time() + $seconds;         
            $this->data[$key]['expirationCallback'] = fn () => time() >= $this->data[$key]['expiresOn'];
        }
    }
}
