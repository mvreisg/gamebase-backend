<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\Provider;

use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\AuthenticationToken;

interface AuthenticationTokenProvider
{
    public function encode(AuthenticationData $data, \DateInterval $duration): string;

    public function decode(string $token): AuthenticationToken;

    public function validate(AuthenticationToken $token): void;
}
