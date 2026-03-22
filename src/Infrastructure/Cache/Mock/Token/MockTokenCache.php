<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\Token;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Exceptions\TokenCacheException;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\Clock\MockTokenCacheClock;

class MockTokenCache implements TokenCacheInterface
{
    private array $data;
    private array $expirationArray;
    private MockTokenCacheClock $clock;

    public function __construct(MockTokenCacheClock $clock)
    {
        $this->data = [];
        $this->expirationArray = [];
        $this->clock = $clock;
    }

    public function set(Username $username, EncodedAuthenticationToken $token): void
    {
        $this->data[$username->getValue()] = $token->getToken();
    }

    public function get(Username $username): EncodedAuthenticationToken
    {
        try {
            $exists = $this->exists($username);
            if ($exists === false) {
                throw new TokenCacheException(
                    "Unexistant key: {$username->getValue()}",
                );
            }
            $expiresIn = $this->expirationArray[$username->getValue()];
            $expired = $this->clock->now()->getTimestamp() >= $expiresIn;
            if ($expired) {
                $this->delete($username);
                throw new TokenCacheException(
                    "Expired: {$username->getValue()}",
                );
            }
            return new EncodedAuthenticationToken($this->data[$username->getValue()]);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function expire(Username $username, \DateInterval $time): void
    {
        try {
            $exists = $this->exists($username);
            if ($exists === false) {
                throw new TokenCacheException(
                    "Unexistant key: {$username->getValue()}",
                );
            }
            $this->expirationArray[$username->getValue()] = $this->clock->now()->add($time)->getTimestamp();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function exists(Username $username): bool
    {
        return isset($this->data[$username->getValue()]);
    }

    public function delete(Username $username): void
    {
        try {
            $exists = $this->exists($username);
            if ($exists === false) {
                throw new TokenCacheException(
                    "Unexistant key: {$username->getValue()}",
                );
            }
            unset(
                $this->data[$username->getValue()],
                $this->expirationArray[$username->getValue()]
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
