<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions;

use Exception;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Enums\InfrastructureExceptionTypesEnum;

class InfrastructureException extends Exception
{
    public function __construct(string $message, InfrastructureExceptionTypesEnum $type, \Throwable|null $previous)
    {
        parent::__construct($message, $type->value, $previous);
    }
}
