<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validate\Exceptions;

class AuthenticationTokenValidatorException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct("Authentication token validator exception: $message");
    }
}
