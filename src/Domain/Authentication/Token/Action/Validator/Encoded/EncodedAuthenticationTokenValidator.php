<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validator\Encoded;

use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;

interface EncodedAuthenticationTokenValidator
{
    public function validate(EncodedAuthenticationToken $token): void;
}
