<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\Exception\EncryptionInterfaceException;

class DefuseEncryption implements EncryptionInterface
{
    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function encrypt(string $text): string
    {
        try {
            $asciiKey = $this->key;
            $key = Key::loadFromAsciiSafeString($asciiKey);
            $encrypted = Crypto::encrypt($text, $key);
            return $encrypted;
        } catch (\Throwable $e) {
            throw new EncryptionInterfaceException(
                $e->getMessage()
            );
        }
    }

    public function decrypt(string $secret): string
    {
        try {
            $asciiKey = $this->key;
            $key = Key::loadFromAsciiSafeString($asciiKey);
            $text = Crypto::decrypt($secret, $key);
            return $text;
        } catch (\Throwable $e) {
            throw new EncryptionInterfaceException(
                $e->getMessage()
            );
        }
    }
}
