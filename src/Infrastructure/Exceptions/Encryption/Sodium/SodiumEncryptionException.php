<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption\Exceptions\Defuse;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Enums\InfrastructureExceptionTypesEnum;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Encryption\EncryptionException;

class SodiumEncryptionException extends EncryptionException
{
    public function __construct(\Throwable|null $cause = null)
    {
        parent::__construct(
            'Encryption error!',
            InfrastructureExceptionTypesEnum::SodiumEncryption,
            $cause
        );
    }
}
