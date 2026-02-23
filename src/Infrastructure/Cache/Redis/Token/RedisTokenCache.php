<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\Token;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Exceptions\TokenCacheException;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Data\Username;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\Predis\PredisConnection;

class RedisTokenCache implements TokenCacheInterface
{
    private PredisConnection $connection;

    public function __construct(PredisConnection $connection)
    {
        $this->connection = $connection;
    }

    public function set(Username $username, EncodedAuthenticationToken $token): void
    {
        $this
            ->connection
            ->get()
            ->set(
                $username->getValue(),
                $token->getToken()
            );
    }

    public function get(Username $username): EncodedAuthenticationToken
    {
        $value = $this
            ->connection
            ->get()
            ->get(
                $username->getValue()
            );
        if ($value === null) {
            throw new TokenCacheException(
                "Unexistant value."
            );
        }
        return new EncodedAuthenticationToken($value);
    }

    public function expire(Username $username, \DateInterval $time): void
    {
        $oneDayInSeconds = 60 * 60 * 24;
        $this
            ->connection
            ->get()
            ->expire(
                $username->getValue(),
                $time->d * $oneDayInSeconds
            );
    }

    public function exists(Username $username): bool
    {
        $exists = $this
            ->connection
            ->get()
            ->exists(
                $username->getValue()
            );
        $exists = boolval($exists);
        return $exists;
    }

    public function delete(Username $username): void
    {
        $status = $this
            ->connection
            ->get()
            ->del(
                $username->getValue()
            );
        $status = boolval($status);
        if ($status === false) {
            throw new TokenCacheException(
                "Unsuccesful deletion."
            );
        }
    }
}
