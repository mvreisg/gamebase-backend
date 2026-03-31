<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Shared\Interface;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
