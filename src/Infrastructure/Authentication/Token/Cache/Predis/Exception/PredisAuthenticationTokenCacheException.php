<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Cache\Predis\Exception;

class PredisAuthenticationTokenCacheException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct("Predis Authentication Token Cache error: $message");
    }
}
