<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Encryption\Interface;

interface EncryptionInterface
{
    public function encrypt(string $text): string;

    public function decrypt(string $secret): string;
}
