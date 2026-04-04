<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Encryption\Interface\Exception;

class EncryptionInterfaceException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct("Encryption interface exception: $message");
    }
}
