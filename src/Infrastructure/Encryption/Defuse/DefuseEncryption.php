<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\Exception\EncryptionInterfaceException;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\Option\DefuseEncryptionOptions;

class DefuseEncryption implements EncryptionInterface
{
    private DefuseEncryptionOptions $options;

    public function __construct(
        DefuseEncryptionOptions $options
    ) {
        $this->options = $options;
    }

    public function encrypt(string $text): string
    {
        try {
            $asciiKey = $this->options->getKey();
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
            $asciiKey = $this->options->getKey();
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
