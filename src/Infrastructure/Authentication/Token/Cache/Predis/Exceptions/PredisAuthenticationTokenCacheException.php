<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Cache\Predis\Exceptions;

class PredisAuthenticationTokenCacheException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct("Predis Authentication Token Cache error: $message");
    }
}
