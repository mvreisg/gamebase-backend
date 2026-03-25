<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Predis\Token;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Exceptions\TokenCacheException;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Predis\Client;

class PredisTokenCache implements TokenCacheInterface
{
    private Client $connection;

    public function __construct(Client $connection)
    {
        $this->connection = $connection;
    }

    public function set(Username $username, EncodedAuthenticationToken $token): void
    {
        $this
            ->connection
            ->set(
                $username->getValue(),
                $token->getToken()
            );
    }

    public function get(Username $username): EncodedAuthenticationToken
    {
        $value = $this
            ->connection
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
        $this
            ->connection
            ->expire(
                $username->getValue(),
                $time->s
            );
    }

    public function exists(Username $username): bool
    {
        $exists = $this
            ->connection
            ->exists(
                $username->getValue()
            );
        $exists = boolval($exists);
        return $exists;
    }

    public function delete(Username $username): bool
    {
        $status = $this
            ->connection
            ->del(
                $username->getValue()
            );
        return boolval($status);
    }
}
