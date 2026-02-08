<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Encryption\Exceptions;

class EncryptionException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct("Encryption exception: $message");
    }
}
