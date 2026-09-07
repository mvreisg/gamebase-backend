<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Encryption\Interface\Exception;

class EncryptionException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
