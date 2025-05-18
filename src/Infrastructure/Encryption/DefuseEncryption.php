<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption;

use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\EncryptionException;
use TypeError;

class DefuseEncryption implements EncryptionInterface
{
    public function encrypt(string $text): string
    {
        try {
            $asciiKey = $_SERVER['DEFUSE_PHP_ENCRYPTION_KEY'];
            $key = Key::loadFromAsciiSafeString($asciiKey);
            $encrypted = Crypto::encrypt($text, $key);
            return $encrypted;
        } catch (
            BadFormatException |
            EnvironmentIsBrokenException |
            TypeError $e
        ) {
            throw new EncryptionException('Ocorreu um erro na criptografia!', 1, $e);
        }
    }

    public function decrypt(string $secret): string
    {
        try {
            $asciiKey = $_SERVER['DEFUSE_PHP_ENCRYPTION_KEY'];
            $key = Key::loadFromAsciiSafeString($asciiKey);
            $text = Crypto::decrypt($secret, $key);
            return $text;
        } catch (
            BadFormatException |
            EnvironmentIsBrokenException |
            WrongKeyOrModifiedCiphertextException |
            TypeError $e
        ) {
            throw new EncryptionException('Ocorreu um erro na criptografia!' . $e->getMessage(), 2, $e);
        }
    }
}
