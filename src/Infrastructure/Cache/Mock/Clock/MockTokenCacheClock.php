<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\Clock;

use Mvreisg\GamebaseBackend\Domain\Interfaces\ClockInterface;

class MockTokenCacheClock implements ClockInterface
{
    private \DateTimeImmutable $now;
    private \DateTimeZone $timezone;

    public function __construct(\DateTimeImmutable $now, \DateTimeZone $timezone)
    {
        $this->timezone = $timezone;
        $this->now = $now->setTimezone($this->timezone);
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }

    public function getTimezone(): \DateTimeZone
    {
        return $this->timezone;
    }

    public function add(\DateInterval $interval): \DateTimeImmutable
    {
        $this->now = $this->now->add($interval);
        return $this->now;
    }

    public function subtract(\DateInterval $interval): \DateTimeImmutable
    {
        $this->now = $this->now->sub($interval);
        return $this->now;
    }
}
