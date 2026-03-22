<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Session\Data\SessionData;

interface AuthenticationTokenEncoder
{
    public function encode(SessionData $data, \DateInterval $duration): EncodedAuthenticationToken;
}
