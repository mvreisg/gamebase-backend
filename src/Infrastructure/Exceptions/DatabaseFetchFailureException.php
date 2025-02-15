<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions;

use Exception;
use Throwable;

/**
 * Database fetch error exception class.
 * Throwed when the PDO falis to fetch data from the database.
 */
class DatabaseFetchFailureException extends Exception
{
    /**
     * Database fetch error exception class constructor.
     * @param string $message The exception message.
     * @param int $code The exception code.
     * @param Throwable|null $cause The exception cause object.
     */
    public function __construct(string $message, int $code = 0, Throwable|null $cause = null)
    {
        parent::__construct($message, $code, $cause);
    }
}
