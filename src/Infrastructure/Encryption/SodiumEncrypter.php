<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption;

use Mvreisg\GamebaseBackend\Domain\Encryption\EncrypterInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\EncryptionErrorException;
use Random\RandomException;
use SodiumException;
use Throwable;

class SodiumEncrypter implements EncrypterInterface
{
    public function encrypt(string $text): string
    {
        try {
            $key = $_SERVER['SODIUM_CRYPTO_SECRETBOX_KEY'];
            $key = sodium_hex2bin($key);
            $nonceBytes = $_SERVER['SODIUM_CRYPTO_SECRETBOX_NONCEBYTES'];
            $nonce = random_bytes($nonceBytes);
            $encryptedText = sodium_crypto_secretbox($text, $nonce, $key);
            $finalText = base64_encode($nonce . $encryptedText);
            return $finalText;
        } catch (RandomException | SodiumException | Throwable $e) {
            throw new EncryptionErrorException('Erro ao fazer a criptografia!', 0, $e);
        }
    }

    public function decrypt(string $secret): string
    {
        try {
            $key = $_SERVER['SODIUM_CRYPTO_SECRETBOX_KEY'];
            $key = sodium_hex2bin($key);
            $nonceBytes = $_SERVER['SODIUM_CRYPTO_SECRETBOX_NONCEBYTES'];
            $decodedText = base64_decode($secret);
            $nonce = mb_substr($decodedText, 0, $nonceBytes, '8bit');
            $encryptedText = mb_substr($decodedText, $nonceBytes, null, '8bit');
            $text = sodium_crypto_secretbox_open($encryptedText, $nonce, $key);
            return $text;
        } catch (SodiumException | Throwable $e) {
            throw new EncryptionErrorException('Erro ao fazer a descriptografia!', 0, $e);
        }
    }
}
