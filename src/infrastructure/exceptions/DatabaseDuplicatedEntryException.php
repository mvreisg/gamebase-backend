<?php
    namespace Gamebase\Infrastructure\Exceptions;

    use Throwable;
    use Exception;

    class DatabaseDuplicatedEntryException extends Exception 
    {
        public function __construct(string $messages, int $code = 0, Throwable|null $cause = null)
        {
            parent::__construct($messages, $code, $cause);
        }
    }
?>