<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption;

use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
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
            EnvironmentIsBrokenException |
            TypeError $e
        ) {
            throw new AuthenticationException('Ocorreu um erro na criptografia!', 1, $e);
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
            EnvironmentIsBrokenException |
            WrongKeyOrModifiedCiphertextException |
            TypeError $e
        ) {
            throw new AuthenticationException('Ocorreu um erro na criptografia!', 2, $e);
        }
    }
}
