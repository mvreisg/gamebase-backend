<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\Exceptions;

class AuthenticationTokenProviderException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct("Authentication token provider exception: $message");
    }
}
