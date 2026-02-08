<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Cache\Token\Exceptions;

class TokenCacheException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct("Token cache error: $message");
    }
}
