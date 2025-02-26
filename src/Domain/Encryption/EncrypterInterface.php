<?php

namespace Mvreisg\GamebaseBackend\Domain\Encryption;

interface EncrypterInterface
{
    public function encrypt(string $text): string;

    public function decrypt(string $secret): string;
}
