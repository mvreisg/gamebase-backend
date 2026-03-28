<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Interfaces\ClockInterface;

class Clock implements ClockInterface
{
    private \DateTimeZone $timezone;

    public function __construct(\DateTimeZone $timezone)
    {
        $this->timezone = $timezone;
    }

    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable()->setTimezone($this->getTimezone());
    }

    public function getTimezone(): \DateTimeZone
    {
        return $this->timezone;
    }
}
