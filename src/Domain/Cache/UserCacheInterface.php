<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Cache;

interface UserCacheInterface
{
    public function set(string $key, string $token): void;

    public function get(string $key): string|null;

    public function expire(string $key, int $seconds): void;

    public function delete(string $key): bool;

    public function exists(string $key): bool;
}
