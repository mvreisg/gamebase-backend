<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Clock;

use Mvreisg\GamebaseBackend\Domain\Interfaces\ClockInterface;

class JwtAuthenticationTokenClock implements ClockInterface
{
    private \DateTimeZone $timezone;

    public function __construct(\DateTimeZone $timezone)
    {
        $this->timezone = $timezone;
    }

    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable()->setTimezone($this->timezone);
    }

    public function getTimezone(): \DateTimeZone
    {
        return $this->timezone;
    }
}
