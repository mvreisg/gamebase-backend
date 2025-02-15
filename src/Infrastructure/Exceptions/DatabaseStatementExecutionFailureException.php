<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions;

use Exception;
use Throwable;

/**
 * Database statement execution failure exception class.
 * Throwed when a statement execution is made and failed on process.
 */
class DatabaseStatementExecutionFailureException extends Exception
{
    /**
     * Database statement execution failure exception class constructor.
     * @param string $message The exception message.
     * @param int $code The exception code.
     * @param Throwable|null $cause The exception cause object.
     */
    public function __construct(string $message, int $code = 0, Throwable|null $cause = null)
    {
        parent::__construct($message, $code, $cause);
    }
}
