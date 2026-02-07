<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Interfaces;

interface Clock
{
    public function now(): \DateTimeImmutable;

    public function getTimezone(): \DateTimeZone;
}
