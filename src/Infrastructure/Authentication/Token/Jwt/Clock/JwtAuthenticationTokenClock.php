<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Clock;

use Mvreisg\GamebaseBackend\Domain\Interfaces\Clock;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

class JwtAuthenticationTokenClock implements Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable("now", $this->getTimezone());
    }

    public function getTimezone(): \DateTimeZone
    {
        return new \DateTimeZone(DotenvEnvironment::get("TIME_ZONE"));
    }
}
