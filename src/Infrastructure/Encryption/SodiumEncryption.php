<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption;

use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\EncryptionErrorException;
use Random\RandomException;
use SodiumException;
use Throwable;

class SodiumEncryption implements EncryptionInterface
{
    public function encrypt(string $text): string
    {
        try {
            $key = $_SERVER['SODIUM_CRYPTO_SECRETBOX_KEY'];
            $key = sodium_hex2bin($key);
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $encrypted = sodium_crypto_secretbox($text, $nonce, $key);
            $secret = base64_encode($nonce . $encrypted);
            return $secret;
        } catch (RandomException | SodiumException | Throwable $e) {
            throw new EncryptionErrorException('Erro ao fazer a criptografia!', 0, $e);
        }
    }

    public function decrypt(string $secret): string
    {
        try {
            $key = $_SERVER['SODIUM_CRYPTO_SECRETBOX_KEY'];
            $key = sodium_hex2bin($key);
            $opened = base64_decode($secret, true);
            if ($opened === false){
                throw new EncryptionErrorException('Erro ao fazer a descriptografia');
            }
            $nonce = substr($opened, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $encrypted = substr($opened, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $text = sodium_crypto_secretbox_open($encrypted, $nonce, $key);
            if ($text === false){
                throw new EncryptionErrorException('Erro ao fazer a descriptografia!');
            }
            return $text;
        } catch (EncryptionErrorException $e){
            throw $e;
        } catch (SodiumException | Throwable $e) {
            throw new EncryptionErrorException('Erro ao fazer a descriptografia!', 0, $e);
        }
    }
}
