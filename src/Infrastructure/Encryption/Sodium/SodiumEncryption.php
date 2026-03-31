<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption\Sodium;

use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\Exception\EncryptionInterfaceException;

class SodiumEncryption implements EncryptionInterface
{
    public function encrypt(string $text): string
    {
        try {
            $key = getenv("SODIUM_CRYPTO_SECRETBOX_KEY");
            $key = sodium_hex2bin($key);
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $encrypted = sodium_crypto_secretbox($text, $nonce, $key);
            $secret = base64_encode($nonce . $encrypted);
            return $secret;
        } catch (\Throwable $e) {
            throw new EncryptionInterfaceException(
                $e->getMessage()
            );
        }
    }

    public function decrypt(string $secret): string
    {
        try {
            $key = getenv("SODIUM_CRYPTO_SECRETBOX_KEY");
            $key = sodium_hex2bin($key);
            $opened = base64_decode($secret, true);
            $nonce = substr($opened, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $encrypted = substr($opened, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $text = sodium_crypto_secretbox_open($encrypted, $nonce, $key);
            return $text;
        } catch (\Throwable $e) {
            throw new EncryptionInterfaceException(
                $e->getMessage()
            );
        }
    }
}
