<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Interfaces;

interface AuthenticationClockInterface
{
    public function now(): \DateTimeImmutable;

    public function add(int $seconds): \DateTimeImmutable;

    public function subtract(int $seconds): \DateTimeImmutable;
}
