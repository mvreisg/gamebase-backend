<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Interfaces;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;

    public function getTimezone(): \DateTimeZone;
}
