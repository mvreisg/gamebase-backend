<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\Exceptions;

class AuthenticationTokenDecoderException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct("Token decode error: $message");
    }
}
