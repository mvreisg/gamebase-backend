<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache;

interface AuthenticationTokenCacheInterface
{
    public function set(string $key, string $token): void;

    public function get(string $key): string;

    public function expire(string $key, \DateInterval $time): void;

    public function exists(string $key): bool;

    public function delete(string $key): bool;
}
