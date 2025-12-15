<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock;

use Mvreisg\GamebaseBackend\Domain\Authentication\Enums\AuthenticationTimesEnum;
use Mvreisg\GamebaseBackend\Domain\Cache\CacheInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\Exceptions\MockCacheException;

class MockUserCache implements CacheInterface
{
    private array $data;
    private array $expirationArray;

    public function __construct()
    {
        $this->data = [];
        $this->expirationArray = [];
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
            $expired = $this->expirationArray[$key]['expireCallback']();
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
            $expirationDate = time() + $seconds;
            $this->expirationArray[$key] = [
                'expire' => $expirationDate,
                'expireCallback' => fn () => time() >= $expirationDate
            ];
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
