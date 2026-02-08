<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\Token;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Exceptions\TokenCacheException;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Data\Username;
use Predis\Client;

class RedisTokenCache implements TokenCacheInterface
{
    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function set(Username $username, EncodedAuthenticationToken $token): void
    {
        $this->redis->set($username->getValue(), $token->getToken());
    }

    public function get(Username $username): EncodedAuthenticationToken
    {
        $value = $this->redis->get($username->getValue());
        if ($value === null) {
            throw new TokenCacheException(
                "value is null"
            );
        }
        return new EncodedAuthenticationToken($value);
    }

    public function expire(Username $username, \DateInterval $time): void
    {
        $oneDayInSeconds = 60 * 60 * 24;
        $this->redis->expire($username->getValue(), $time->d * $oneDayInSeconds);
    }

    public function exists(Username $username): bool
    {
        $exists = $this->redis->exists($username->getValue());
        $exists = boolval($exists);
        return $exists;
    }

    public function delete(Username $username): void
    {
        $status = $this->redis->del($username->getValue());
        $status = boolval($status);
        if ($status === false) {
            throw new TokenCacheException(
                "Redis delete error: unsuccesful deletion."
            );
        }
    }
}
