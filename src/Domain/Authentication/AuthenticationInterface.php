<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication;

use Mvreisg\GamebaseBackend\Domain\Authentication\Enums\AuthenticationTimesEnum;

interface AuthenticationInterface
{
    public function encode(string $username, AuthenticationTimesEnum $time): string;

    public function decode(string $token): mixed;
}
