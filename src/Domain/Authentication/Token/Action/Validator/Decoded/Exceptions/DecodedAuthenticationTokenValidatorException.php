<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validator\Decoded\Exceptions;

class DecodedAuthenticationTokenValidatorException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct("Decoded authentication token validator exception: $message");
    }
}
