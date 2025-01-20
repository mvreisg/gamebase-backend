<?php
    namespace Gamebase\Domain\Exceptions;

    use Throwable;
    use Exception;

    class EntityInvalidValueException extends Exception 
    {
        public function __construct(string $messages, int $code = 0, Throwable|null $cause = null)
        {
            parent::__construct($messages, $code, $cause);
        }
    }
?>