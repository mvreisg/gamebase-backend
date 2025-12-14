<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis;

use Mvreisg\GamebaseBackend\Domain\Authentication\Enums\AuthenticationTimesEnum;
use Mvreisg\GamebaseBackend\Domain\Cache\CacheInterface;
use Mvreisg\GamebaseBackend\Domain\Cache\Enums\CacheInterfaceDeletionStatesEnum;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\Exceptions\RedisCacheException;
use Predis\Client;

class RedisUserCache implements CacheInterface
{
    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function set(string $key, string $value): void
    {
        try {
            $status = $this->redis->set($key, $value);
            if ($status === null) {
                throw new RedisCacheException(
                    "Redis set error: status is null.",
                );
            }
            $status->getPayload();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function get(string $key): string
    {
        try {
            $value = $this->redis->get($key);
            if ($value === null) {
                throw new RedisCacheException(
                    "Redis get error: value is null.",
                );
            }
            return $value;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function expire(string $key, AuthenticationTimesEnum $time): void
    {
        try {
            $seconds = null;
            switch ($time) {
                case AuthenticationTimesEnum::OneDay:
                    $seconds = 60 * 60 * 24;
                    break;
                case AuthenticationTimesEnum::OneWeek:
                    $seconds = 60 * 60 * 24 * 7;
                    break;
                default:
                    throw new RedisCacheException(
                        "Redis expire error: untreated expire time.",
                    );
            }
            $status = $this->redis->expire($key, $seconds);
            $status = boolval($status);
            if ($status === false) {
                throw new RedisCacheException(
                    "Redis expire error: status is false.",
                );
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function exists(string $key): bool
    {
        try {
            $exists = $this->redis->exists($key);
            $exists = boolval($exists);
            return $exists;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(string $key): CacheInterfaceDeletionStatesEnum
    {
        try {
            $exists = $this->exists($key);
            if ($exists === false) {
                return CacheInterfaceDeletionStatesEnum::Unexistant;
            }
            $status = $this->redis->del($key);
            $status = boolval($status);
            if ($status === false) {
                return CacheInterfaceDeletionStatesEnum::Error;
            }
            return CacheInterfaceDeletionStatesEnum::Success;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
