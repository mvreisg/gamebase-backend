<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Cache;

use Mvreisg\GamebaseBackend\Domain\Authentication\Enums\AuthenticationTimesEnum;

interface CacheInterface
{
    public function set(string $key, string $token): void;

    public function get(string $key): string;

    public function expire(string $key, AuthenticationTimesEnum $time): void;

    public function delete(string $key): void;

    public function exists(string $key): bool;
}
