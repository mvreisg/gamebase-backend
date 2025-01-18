<?php
    namespace Gamebase\Domain\Exceptions;

    use Throwable;
    use Exception;

    class InvalidValueException extends Exception 
    {
        public function __construct(string $message, int $code = 0, Throwable|null $cause = null)
        {
            parent::__construct($message, $code, $cause);
        }
    }
?>