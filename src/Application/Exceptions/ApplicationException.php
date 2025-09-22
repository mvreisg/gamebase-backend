<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Exceptions;

use Exception;
use Mvreisg\GamebaseBackend\Application\Exceptions\Enums\ApplicationExceptionTypesEnum;

class ApplicationException extends Exception
{
    public function __construct(string $message, ApplicationExceptionTypesEnum $type, \Throwable|null $cause = null)
    {
        parent::__construct($message, $type->value, $cause);
    }
}
