<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder;

use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;

interface AuthenticationTokenEncoder
{
    public function encode(AuthenticationData $data, \DateInterval $duration): EncodedAuthenticationToken;
}
