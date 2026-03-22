<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\Exceptions;

class AuthenticationTokenEncoderException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct("Token encode error: $message");
    }
}
