<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\Exceptions;

use Mvreisg\GamebaseBackend\Domain\Encryption\Exceptions\EncryptionException;

class DefuseEncryptionException extends EncryptionException
{
    public const EXCEPTION_CODE = 0;

    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct(
            $message,
            self::EXCEPTION_CODE,
            $previous
        );
    }
}
