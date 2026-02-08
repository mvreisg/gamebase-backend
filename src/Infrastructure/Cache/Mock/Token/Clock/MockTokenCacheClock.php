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

    public function advance(\DateInterval $interval): void
    {
        $this->now = $this->now->add($interval);
    }

    public function rewind(\DateInterval $interval): void
    {
        $this->now = $this->now->sub($interval);
    }
}
