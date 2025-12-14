<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock;

use Mvreisg\GamebaseBackend\Domain\Authentication\Enums\AuthenticationTimesEnum;
use Mvreisg\GamebaseBackend\Domain\Cache\CacheInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\Exceptions\MockCacheException;

class MockUserCache implements CacheInterface
{
    private array $keyValues;
    private array $data;

    public function __construct()
    {
        try {
            $this->keyValues = [];
            $this->data = [];
        } catch (\Throwable $e) {
            throw new MockCacheException(
                "Mock constructor error: {$e->getMessage()}",
                $e
            );
        }
    }

    public function set(string $key, string $token): void
    {
        try {
            $this->keyValues[$key] = $token;
        } catch (\Throwable $e) {
            throw new MockCacheException(
                "Mock set error: {$e->getMessage()}",
                $e
            );
        }
    }

    public function get(string $key): ?string
    {
        try {
            if ($this->exists($key)) {
                return $this->keyValues[$key];
            }
            return null;
        } catch (\Throwable $e) {
            throw new MockCacheException(
                "Mock get error: {$e->getMessage()}",
                $e
            );
        }
    }

    public function exists(string $key): bool
    {
        try {
            return isset($this->keyValues[$key]);
        } catch (\Throwable $e) {
            throw new MockCacheException(
                "Mock exists error: {$e->getMessage()}",
                $e
            );
        }
    }

    public function delete(string $key): bool
    {
        try {
            if ($this->exists($key)) {
                unset($this->keyValues[$key]);
                return true;
            }
            return false;
        } catch (\Throwable $e) {
            throw new MockCacheException(
                "Mock delete error: {$e->getMessage()}",
                $e
            );
        }
    }

    public function expire(string $key, AuthenticationTimesEnum $time): void
    {
        try {
            if ($this->exists($key)) {
                $seconds = null;
                switch ($time) {
                    case AuthenticationTimesEnum::OneDay:
                        $seconds = 60 * 60 * 24;
                        break;
                    case AuthenticationTimesEnum::OneWeek:
                        $seconds = 60 * 60 * 24 * 7;
                        break;
                    default:
                        throw new \RuntimeException(
                            "Untreated time: $time"
                        );
                }
                $this->data[$key]['expiresOn'] = time() + $seconds;
                $this->data[$key]['expirationCallback'] = fn () => time() >= $this->data[$key]['expiresOn'];
            }
        } catch (\Throwable $e) {
            throw new MockCacheException(
                $e->getMessage(),
                $e
            );
        }
    }
}
