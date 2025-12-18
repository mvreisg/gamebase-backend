<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Entities;

use Mvreisg\GamebaseBackend\Domain\Authentication\Interfaces\AuthenticationClockInterface;

class MockTokenAuthenticationClock implements AuthenticationClockInterface
{
    private \DateTimeImmutable $now;

    public function __construct(\DateTimeImmutable $now)
    {
        $this->now = $now;
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
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

    public function toTheFuture(int $seconds): \DateTimeImmutable
    {
        $this->now = $this->add($seconds);
        return $this->now();
    }

    public function toThePast(int $seconds): \DateTimeImmutable
    {
        $this->now = $this->subtract($seconds);
        return $this->now();
    }
}
