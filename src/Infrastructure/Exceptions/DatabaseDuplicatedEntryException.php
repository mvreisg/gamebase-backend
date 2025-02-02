<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions;

use Exception;
use Throwable;

class DatabaseDuplicatedEntryException extends Exception
{
    public function __construct(string $messages, int $code = 0, Throwable|null $cause = null)
    {
        parent::__construct($messages, $code, $cause);
    }
}
