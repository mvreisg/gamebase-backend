<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions;

use Exception;
use Throwable;

/**
 * Database transaction creation failure exception class.
 * Throwed when the PDO tries to create a tarnsaction then fails.
 */
class DatabaseTransactionCreationFailureException extends Exception
{
    /**
     * Database transaction creation failure exception class constructor.
     * @param string $message The exception message.
     * @param int $code The exception code.
     * @param Throwable|null $cause The exception cause object.
     */
    public function __construct(string $message, int $code = 0, Throwable|null $cause = null)
    {
        parent::__construct($message, $code, $cause);
    }
}
