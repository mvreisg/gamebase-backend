<?php

declare(strict_types=1);

use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Hashing\Interface\HashingInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\Option\DefuseEncryptionOptions;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Sodium\Option\SodiumEncryptionOptions;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Sodium\SodiumEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Hashing\Sodium\SodiumHashing;

try {
    return [
        "encryption.defuse.key" => fn () => $_ENV["DEFUSE_PHP_ENCRYPTION_KEY"],
        "encryption.sodium.key" => fn () => $_ENV["SODIUM_CRYPTO_SECRETBOX_KEY"],

        "repository.adapter" => fn () => $_ENV["REPOSITORY_ADAPTER"],
        "repository.host" => fn () => $_ENV["REPOSITORY_HOST"],
        "repository.port" => fn () => $_ENV["REPOSITORY_PORT"],
        "repository.database" => fn () => $_ENV["REPOSITORY_DATABASE"],
        "repository.username" => fn () => $_ENV["REPOSITORY_USERNAME"],
        "repository.password" => fn () => $_ENV["REPOSITORY_PASSWORD"],
        "repository.charset" => fn () => $_ENV["REPOSITORY_CHARSET"],
        "repository.root.username" => fn () => $_ENV["REPOSITORY_ROOT_USERNAME"],
        "repository.root.password" => fn () => $_ENV["REPOSITORY_ROOT_PASSWORD"],

        HashingInterface::class => DI\get(SodiumHashing::class),

        EncryptionInterface::class => DI\get(SodiumEncryption::class),

        SodiumEncryptionOptions::class => DI\autowire()
            ->constructorParameter("key", DI\get("encryption.sodium.key")),

        DefuseEncryptionOptions::class => DI\autowire()
            ->constructorParameter("key", DI\get("encryption.defuse.key")),
    ];
} catch (\Throwable $e) {
    throw $e;
}
