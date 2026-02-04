<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\Token\Clock;

use Mvreisg\GamebaseBackend\Domain\Interfaces\Clock;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

class MockTokenCacheClock implements Clock
{
    private \DateTimeImmutable $now;

    public function __construct(\DateTimeImmutable $now)
    {
        $this->now = $now->setTimezone($this->getTimezone());
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }

    public function getTimezone(): \DateTimeZone
    {
        return new \DateTimeZone(DotenvEnvironment::get("TIME_ZONE"));
    }

    public function getTimeBasedOnTimestamp(int $timestamp): \DateTimeImmutable
    {
        return $this->now->setTimestamp($timestamp);
    }
}
