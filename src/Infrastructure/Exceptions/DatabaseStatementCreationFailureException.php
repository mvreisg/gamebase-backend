<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions;

use Exception;
use Throwable;

/**
 * Database statement creation failure exception class.
 * Throwed when PDO fails to create the statement.
 */
class DatabaseStatementCreationFailureException extends Exception
{
    /**
     * Database statement creation failure exception class constructor.
     * @param string $message The exception message.
     * @param int $code The exception code.
     * @param Throwable|null $cause The exception cause object.
     */
    public function __construct(string $message, int $code = 0, Throwable|null $cause = null)
    {
        parent::__construct($message, $code, $cause);
    }
}
