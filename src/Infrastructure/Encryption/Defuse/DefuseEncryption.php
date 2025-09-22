<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse;

use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Encryption\Defuse\DefuseEncryptionException;

class DefuseEncryption implements EncryptionInterface
{
    public function encrypt(string $text): string
    {
        try {
            $asciiKey = DotenvEnvironment::get('DEFUSE_PHP_ENCRYPTION_KEY');
            $key = Key::loadFromAsciiSafeString($asciiKey);
            $encrypted = Crypto::encrypt($text, $key);
            return $encrypted;
        } catch (\Throwable $e) {
            throw new DefuseEncryptionException(
                $e
            );
        }
    }

    public function decrypt(string $secret): string
    {
        try {
            $asciiKey = DotenvEnvironment::get('DEFUSE_PHP_ENCRYPTION_KEY');
            $key = Key::loadFromAsciiSafeString($asciiKey);
            $text = Crypto::decrypt($secret, $key);
            return $text;
        } catch (\Throwable $e) {
            throw new DefuseEncryptionException(
                $e
            );
        }
    }
}
