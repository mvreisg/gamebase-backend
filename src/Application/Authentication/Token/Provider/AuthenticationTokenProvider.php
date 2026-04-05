<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider;

use Mvreisg\GamebaseBackend\Application\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\AuthenticationToken;

interface AuthenticationTokenProvider
{
    public function encode(AuthenticationData $data, \DateInterval $duration): string;

    public function decode(string $token): AuthenticationToken;

    public function validate(AuthenticationToken $token): void;
}
