<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Exceptions;

use Exception;
use Mvreisg\GamebaseBackend\Domain\Exceptions\Enums\DomainExceptionTypesEnum;

class DomainException extends Exception
{
    public function __construct(string $message, DomainExceptionTypesEnum $type, \Throwable|null $previous)
    {
        parent::__construct($message, $type->value, $previous);
    }
}
