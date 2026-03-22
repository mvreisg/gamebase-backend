<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse;

use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class DefuseEncryption implements EncryptionInterface
{
    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function encrypt(string $text): string
    {
        $asciiKey = $this->key;
        $key = Key::loadFromAsciiSafeString($asciiKey);
        $encrypted = Crypto::encrypt($text, $key);
        return $encrypted;
    }

    public function decrypt(string $secret): string
    {
        $asciiKey = $this->key;
        $key = Key::loadFromAsciiSafeString($asciiKey);
        $text = Crypto::decrypt($secret, $key);
        return $text;
    }
}
