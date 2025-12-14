<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Cache;

use Mvreisg\GamebaseBackend\Domain\Authentication\Enums\AuthenticationTimesEnum;
use Mvreisg\GamebaseBackend\Domain\Cache\Enums\CacheInterfaceDeletionStatesEnum;

interface CacheInterface
{
    public function set(string $key, string $token): void;

    public function get(string $key): string;

    public function expire(string $key, AuthenticationTimesEnum $time): void;

    public function delete(string $key): CacheInterfaceDeletionStatesEnum;

    public function exists(string $key): bool;
}
