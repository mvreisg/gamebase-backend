<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded\DecodedAuthenticationToken;

interface AuthenticationTokenDecoder
{
    public function decode(EncodedAuthenticationToken $token): DecodedAuthenticationToken;
}
