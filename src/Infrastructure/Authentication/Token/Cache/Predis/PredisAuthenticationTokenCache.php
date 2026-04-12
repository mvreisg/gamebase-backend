<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Cache\Predis;

use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\Exception\AuthenticationTokenCacheException;
use Mvreisg\GamebaseBackend\Infrastructure\Time\Duration;
use Predis\Client;

class PredisAuthenticationTokenCache implements AuthenticationTokenCacheInterface
{
    private Client $connection;

    public function __construct(Client $connection)
    {
        $this->connection = $connection;
    }

    public function set(string $key, string $token): void
    {
        $this->connection->set(
            $key,
            $token
        );
    }

    public function get(string $key): string
    {
        $value = $this->connection->get(
            $key
        );
        if ($value === null) {
            throw new AuthenticationTokenCacheException(
                "Unexistant value."
            );
        }
        return $value;
    }

    public function expire(string $key, \DateInterval $time): void
    {
        $this->connection->expire(
            $key,
            $time->d * Duration::ONE_DAY_IN_SECONDS
        );
    }

    public function exists(string $key): bool
    {
        $exists = $this->connection->exists(
            $key
        );
        $exists = boolval($exists);
        return $exists;
    }

    public function delete(string $key): bool
    {
        $status = $this->connection->del(
            $key
        );
        return boolval($status);
    }
}
