<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication;

use Mvreisg\GamebaseBackend\Domain\Authentication\DTOs\AuthenticationPayloadValueDTO;
use Mvreisg\GamebaseBackend\Domain\Authentication\Enums\AuthenticationTimesEnum;
use Mvreisg\GamebaseBackend\Domain\Authentication\Interfaces\AuthenticationClockInterface;
use Mvreisg\GamebaseBackend\Domain\Authentication\ValueObjects\AuthenticationPayloadValueObject;

interface AuthenticationInterface
{
    public function encode(
        AuthenticationPayloadValueDTO $dto,
        AuthenticationTimesEnum $time,
        AuthenticationClockInterface $clock
    ): string;

    public function decode(
        string $token,
        AuthenticationClockInterface $clock
    ): AuthenticationPayloadValueObject;
}
