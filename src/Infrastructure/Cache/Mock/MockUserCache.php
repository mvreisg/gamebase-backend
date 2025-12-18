<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock;

use Mvreisg\GamebaseBackend\Domain\Authentication\Enums\AuthenticationTimesEnum;
use Mvreisg\GamebaseBackend\Domain\Cache\CacheInterface;
use Mvreisg\GamebaseBackend\Domain\Cache\Interfaces\CacheClockInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\Exceptions\MockCacheException;

class MockUserCache implements CacheInterface
{
    private array $data;
    private array $expirationArray;
    private CacheClockInterface $clock;

    public function __construct(CacheClockInterface $clock)
    {
        $this->data = [];
        $this->expirationArray = [];
        $this->clock = $clock;
    }

    public function set(string $key, string $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key): string
    {
        try {
            $exists = $this->exists($key);
            if ($exists === false) {
                throw new MockCacheException(
                    "Mock get error: Unexistant key $key",
                );
            }
            $expiresIn = $this->expirationArray[$key];
            //echo $this->clock->now()->getTimestamp() - $expiresIn;
            $expired = $this->clock->now()->getTimestamp() >= $expiresIn;
            if ($expired) {
                $this->delete($key);
                throw new MockCacheException(
                    "Mock get error: Expired key $key",
                );
            }
            return $this->data[$key];
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function expire(string $key, AuthenticationTimesEnum $time): void
    {
        try {
            $exists = $this->exists($key);
            if ($exists === false) {
                throw new MockCacheException(
                    "Mock expire error: Unexistant key $key",
                );
            }
            $seconds = null;
            switch ($time) {
                case AuthenticationTimesEnum::OneDay:
                    $seconds = 60 * 60 * 24;
                    break;
                case AuthenticationTimesEnum::OneWeek:
                    $seconds = 60 * 60 * 24 * 7;
                    break;
                default:
                    throw new MockCacheException(
                        "Mock expire error: Untreated time: $time"
                    );
            }
            $this->expirationArray[$key] = $this->clock->add($seconds)->getTimestamp();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function exists(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function delete(string $key): void
    {
        try {
            $exists = $this->exists($key);
            if ($exists === false) {
                throw new MockCacheException(
                    "Mock expire error: Unexistant key $key",
                );
            }
            unset(
                $this->data[$key],
                $this->expirationArray[$key]
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
