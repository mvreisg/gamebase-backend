<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Entities;

use Mvreisg\GamebaseBackend\Domain\Authentication\Interfaces\AuthenticationClockInterface;

class JwtTokenAuthenticationClock implements AuthenticationClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    public function add(int $seconds): \DateTimeImmutable
    {
        $interval = new \DateInterval("PT{$seconds}S");
        $now = $this->now()->add($interval);
        return $now;
    }

    public function subtract(int $seconds): \DateTimeImmutable
    {
        $interval = new \DateInterval("PT{$seconds}S");
        $now = $this->now()->sub($interval);
        return $now;
    }
}
