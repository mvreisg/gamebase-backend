<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis;

use Mvreisg\GamebaseBackend\Domain\Cache\UserCacheInterface;
use Predis\Client;

class RedisUserCache implements UserCacheInterface
{
    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function set(string $userName, mixed $cache): void
    {
        $this->redis->set($userName, $cache);
    }

    public function get(string $userName): string|null
    {
        return $this->redis->get($userName);
    }
}
