<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token;

use Mvreisg\GamebaseBackend\Domain\Authentication\Enums\AuthenticationTimesEnum;

interface TokenAuthenticationInterface
{
    public function encode(string $userName, AuthenticationTimesEnum $time): string;

    public function decode(string $token): mixed;
}
