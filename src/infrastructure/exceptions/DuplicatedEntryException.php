<?php
    namespace Gamebase\Infrastructure\Exceptions;

    use Throwable;
    use Exception;

    class DuplicatedEntryException extends Exception 
    {
        public function __construct(string $message, int $code = 0, Throwable|null $cause = null)
        {
            parent::__construct($message, $code, $cause);
        }
    }
?>