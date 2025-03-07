<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption;

use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\EncryptionException;
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
        } catch (RandomException $e) {
            throw new EncryptionException('Erro ao fazer a criptografia!', 1, $e);
        } catch (SodiumException $e) {
            throw new EncryptionException('Erro ao fazer a criptografia!', 2, $e);
        } catch (Throwable $e) {
            throw new EncryptionException('Erro ao fazer a criptografia!', 3, $e);
        }
    }

    public function decrypt(string $secret): string
    {
        try {
            $key = $_SERVER['SODIUM_CRYPTO_SECRETBOX_KEY'];
            $key = sodium_hex2bin($key);
            $opened = base64_decode($secret, true);
            if ($opened === false) {
                throw new EncryptionException('Erro ao fazer a descriptografia');
            }
            $nonce = substr($opened, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $encrypted = substr($opened, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $text = sodium_crypto_secretbox_open($encrypted, $nonce, $key);
            if ($text === false) {
                throw new EncryptionException('Erro ao fazer a descriptografia!');
            }
            return $text;
        } catch (EncryptionException $e) {
            throw $e;
        } catch (SodiumException $e) {
            throw new EncryptionException('Erro ao fazer a descriptografia!', 4, $e);
        } catch (Throwable $e) {
            throw new EncryptionException('Erro ao fazer a descriptografia!', 5, $e);
        }
    }
}
