<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions;

use Exception;
use Throwable;

/**
 * Database duplicated entry exception class.
 * Throwed when a register already exists in the database.
 */
class DatabaseUnexistantValueException extends Exception
{
    /**
     * Database duplicated entry exception class. constructor.
     * @param string $message The exception message.
     * @param int $code The exception code.
     * @param Throwable|null $cause The exception cause object.
     */
    public function __construct(string $message, int $code = 0, Throwable|null $cause = null)
    {
        parent::__construct($message, $code, $cause);
    }
}
