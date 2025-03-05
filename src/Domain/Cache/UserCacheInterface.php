<?php

namespace Mvreisg\GamebaseBackend\Domain\Cache;

interface UserCacheInterface
{
    public function set(string $userName, mixed $token): void;

    public function get(string $userName): string|null;
}
