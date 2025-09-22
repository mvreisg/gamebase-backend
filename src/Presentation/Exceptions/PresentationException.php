<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Exceptions;

use Exception;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Enums\PresentationExceptionTypesEnum;

class PresentationException extends Exception
{
    public function __construct(
        string $message,
        PresentationExceptionTypesEnum $type,
        \Throwable|null $previous = null
    ) {
        parent::__construct($message, $type->value, $previous);
    }
}
