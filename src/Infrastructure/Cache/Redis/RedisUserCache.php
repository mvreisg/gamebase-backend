<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis;

use Mvreisg\GamebaseBackend\Domain\Cache\CacheInterface;
use Predis\Client;

class RedisUserCache implements CacheInterface
{
    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function set(string $userName, string $token): void
    {
        $this->redis->set($userName, $token);
    }

    public function get(string $userName): string|null
    {
        return $this->redis->get($userName);
    }

    public function expire(string $key, int $seconds): void
    {
        $this->redis->expire($key, $seconds);
    }

    public function exists(string $key): bool
    {
        return boolval($this->redis->exists($key));
    }

    public function delete(string $key): bool
    {
        if ($this->exists($key)) {
            $this->redis->del($key);
            return true;
        }
        return false;
    }
}
