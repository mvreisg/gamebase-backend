<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validator\Decoded;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Decoded\DecodedAuthenticationToken;

interface DecodedAuthenticationTokenValidator
{
    public function validate(DecodedAuthenticationToken $token): void;
}
