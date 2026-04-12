<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\Exception;

class AuthenticationTokenCacheException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct("Authentication Token Cache error: $message");
    }
}
