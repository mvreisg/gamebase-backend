<?php

declare(strict_types=1);

use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;

try {
    return [
        "encryption.defuse.key" => fn () => $_ENV["DEFUSE_PHP_ENCRYPTION_KEY"],

        "repository.adapter" => fn () => $_ENV["REPOSITORY_ADAPTER"],
        "repository.host" => fn () => $_ENV["REPOSITORY_HOST"],
        "repository.port" => fn () => $_ENV["REPOSITORY_PORT"],
        "repository.database" => fn () => $_ENV["REPOSITORY_DATABASE"],
        "repository.username" => fn () => $_ENV["REPOSITORY_USERNAME"],
        "repository.password" => fn () => $_ENV["REPOSITORY_PASSWORD"],
        "repository.charset" => fn () => $_ENV["REPOSITORY_CHARSET"],
        "repository.root.username" => fn () => $_ENV["REPOSITORY_ROOT_USERNAME"],
        "repository.root.password" => fn () => $_ENV["REPOSITORY_ROOT_PASSWORD"],

        EncryptionInterface::class => DI\get(DefuseEncryption::class),

        DefuseEncryption::class => DI\autowire()
            ->constructorParameter("key", DI\get("encryption.defuse.key")),
    ];
} catch (\Throwable $e) {
    throw $e;
}
