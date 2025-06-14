<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Encryption;

interface EncryptionInterface
{
    public function encrypt(string $text): string;

    public function decrypt(string $secret): string;
}
