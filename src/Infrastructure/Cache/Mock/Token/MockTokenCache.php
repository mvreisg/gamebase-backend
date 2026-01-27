<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Data\Username;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\User\Clock\MockUserCacheClock;

class MockTokenCache implements TokenCacheInterface
{
    private array $data;
    private array $expirationArray;
    private MockUserCacheClock $clock;

    public function __construct(MockUserCacheClock $clock)
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
                throw new \DomainException(
                    "Mock get error: Unexistant username $username",
                );
            }
            $expiresIn = $this->expirationArray[$username];
            $expired = $this->clock->now()->getTimestamp() >= $expiresIn;
            if ($expired) {
                $this->delete($username);
                throw new \DomainException(
                    "Mock get error: Expired username $username",
                );
            }
            return new EncodedAuthenticationToken($this->data[$username]);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function expire(Username $username, \DateInterval $time): void
    {
        try {
            $exists = $this->exists($username);
            if ($exists === false) {
                throw new \DomainException(
                    "Mock expire error: Unexistant username $username",
                );
            }
            $this->expirationArray[$username] = $this->clock->now()->add($time)->getTimestamp();
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
                throw new \DomainException(
                    "Mock expire error: Unexistant username $username",
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
