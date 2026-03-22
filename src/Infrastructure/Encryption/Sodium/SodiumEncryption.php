<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption\Sodium;

use Mvreisg\GamebaseBackend\Domain\Encryption\Exceptions\EncryptionException;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

class SodiumEncryption implements EncryptionInterface
{
    public function encrypt(string $text): string
    {
        $key = getenv("SODIUM_CRYPTO_SECRETBOX_KEY");
        $key = sodium_hex2bin($key);
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $encrypted = sodium_crypto_secretbox($text, $nonce, $key);
        $secret = base64_encode($nonce . $encrypted);
        return $secret;
    }

    public function decrypt(string $secret): string
    {
        $key = getenv("SODIUM_CRYPTO_SECRETBOX_KEY");
        $key = sodium_hex2bin($key);
        $opened = base64_decode($secret, true);
        if ($opened === false) {
            throw new EncryptionException(
                "The secret is not a valid base64 encoded string."
            );
        }
        $nonce = substr($opened, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $encrypted = substr($opened, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $text = sodium_crypto_secretbox_open($encrypted, $nonce, $key);
        if ($text === false) {
            throw new EncryptionException(
                "The secret could not be decrypted."
            );
        }
        return $text;
    }
}
