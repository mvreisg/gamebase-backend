<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\Exceptions;

use Mvreisg\GamebaseBackend\Domain\Cache\Exceptions\CacheException;

class RedisCacheException extends CacheException
{
    public const EXCEPTION_CODE = 0;

    public function __construct(string $message = "", ?\Throwable $previous = null)
    {
        parent::__construct(
            $message,
            self::EXCEPTION_CODE,
            $previous
        );
    }
}
