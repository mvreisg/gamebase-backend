<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Encryption;

use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Sodium\SodiumEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

class EncryptionAdapter
{
    private EncryptionInterface $encrypter;

    public function __construct()
    {
        switch (DotenvEnvironment::get("ENCRYPTION_METHOD")) {
            case "sodium":
                $this->encrypter = new SodiumEncryption();
                break;
            case "defuse":
                $this->encrypter = new DefuseEncryption();
                break;
            default:
                throw new \DomainException("Invalid encryption method");
        }
    }

    public function encrypt(string $data): string
    {
        return $this->encrypter->encrypt($data);
    }

    public function decrypt(string $data): string
    {
        return $this->encrypter->decrypt($data);
    }
}
