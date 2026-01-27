<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse;

use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

class DefuseEncryption implements EncryptionInterface
{
    public function encrypt(string $text): string
    {
        $asciiKey = DotenvEnvironment::get("DEFUSE_PHP_ENCRYPTION_KEY");
        $key = Key::loadFromAsciiSafeString($asciiKey);
        $encrypted = Crypto::encrypt($text, $key);
        return $encrypted;
    }

    public function decrypt(string $secret): string
    {
        $asciiKey = DotenvEnvironment::get("DEFUSE_PHP_ENCRYPTION_KEY");
        $key = Key::loadFromAsciiSafeString($asciiKey);
        $text = Crypto::decrypt($secret, $key);
        return $text;
    }
}
