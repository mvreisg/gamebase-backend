<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities\Sector\Exceptions;

class SectorInvalidIdException extends \Exception
{
    public const EXCEPTION_CODE = 0;

    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct(
            $message,
            self::EXCEPTION_CODE,
            $previous
        );
    }
}
