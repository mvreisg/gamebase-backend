<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Exceptions;

class HttpInternalServerError extends \Exception
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
