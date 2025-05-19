<?php

namespace Mvreisg\GamebaseBackend\Domain\Cache;

interface UserCacheInterface
{
    public function set(string $userName, mixed $token): void;

    public function get(string $userName): string|null;

    public function expire(string $key, int $seconds): void;

    public function delete(string $key): bool;

    public function exists(string $key): bool;
}
