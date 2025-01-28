<?php
namespace Mvreisg\GamebaseBackend\Domain\Exceptions;

use Exception;
use Throwable;

class EntityInvalidValueException extends Exception
{
    public function __construct(string $messages, int $code = 0, Throwable|null $cause = null)
    {
        parent::__construct($messages, $code, $cause);
    }
}
