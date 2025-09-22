<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock;

use Mvreisg\GamebaseBackend\Domain\Cache\CacheInterface;

class MockUserCache implements CacheInterface
{
    private array $keyValues;
    private array $data;

    public function __construct()
    {
        $this->keyValues = [];
        $this->data = [];
    }

    public function set(string $key, string $token): void
    {
        $this->keyValues[$key] = $token;
    }

    public function get(string $key): string|null
    {
        if ($this->exists($key)) {
            return $this->keyValues[$key];
        }
        return null;
    }

    public function exists(string $key): bool
    {
        return isset($this->keyValues[$key]);
    }

    public function delete(string $key): bool
    {
        if ($this->exists($key)) {
            unset($this->keyValues[$key]);
            return true;
        }
        return false;
    }

    public function expire(string $key, int $seconds): void
    {
        if ($this->exists($key)) {
            $this->data[$key]['expiresOn'] = time() + $seconds;
            $this->data[$key]['expirationCallback'] = fn () => time() >= $this->data[$key]['expiresOn'];
        }
    }
}
