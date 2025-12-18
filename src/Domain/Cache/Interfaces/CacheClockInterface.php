<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Cache\Interfaces;

interface CacheClockInterface
{
    public function now(): \DateTimeImmutable;

    public function add(int $seconds): \DateTimeImmutable;

    public function subtract(int $seconds): \DateTimeImmutable;
}
