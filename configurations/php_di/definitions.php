<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validator\Decoded\DecodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validator\Encoded\EncodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Decoder\JwtAuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Encoder\JwtAuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Validator\Decoded\JwtDecodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Validator\Encoded\JwtEncodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Predis\Token\PredisTokenCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbGameGenreRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbGamePlatformRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbGameRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbGenreRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbPlatformRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbSectorRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbUserRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbUserSectorPermissionRepository;
use Predis\Client;

try {
    return [
        "timezone" => fn () => $_ENV["TIME_ZONE"],

        "encryption.defuse.key" => fn () => $_ENV["DEFUSE_PHP_ENCRYPTION_KEY"],
        "encryption.sodium.key" => fn () => $_ENV["SODIUM_CRYPTO_SECRETBOX_KEY"],

        "authentication.jwt.key" => fn () => $_ENV["JWT_SECRET"],

        "repository.adapter" => fn () => $_ENV["REPOSITORY_ADAPTER"],
        "repository.host" => fn () => $_ENV["REPOSITORY_HOST"],
        "repository.port" => fn () => $_ENV["REPOSITORY_PORT"],
        "repository.database" => fn () => $_ENV["REPOSITORY_DATABASE"],
        "repository.username" => fn () => $_ENV["REPOSITORY_USERNAME"],
        "repository.password" => fn () => $_ENV["REPOSITORY_PASSWORD"],
        "repository.charset" => fn () => $_ENV["REPOSITORY_CHARSET"],
        "repository.root.username" => fn () => $_ENV["REPOSITORY_ROOT_USERNAME"],
        "repository.root.password" => fn () => $_ENV["REPOSITORY_ROOT_PASSWORD"],

        "cache.redis.scheme" => fn () => $_ENV["REDIS_SCHEME"],
        "cache.redis.host" => fn () => $_ENV["REDIS_HOST"],
        "cache.redis.port" => fn () => $_ENV["REDIS_PORT"],

        EncryptionInterface::class => DI\get(DefuseEncryption::class),

        DefuseEncryption::class => DI\autowire()
            ->constructorParameter("key", DI\get("encryption.defuse.key")),

        JwtAuthenticationTokenDecoder::class => DI\autowire()
            ->constructorParameter("key", DI\get("authentication.jwt.key")),

        JwtAuthenticationTokenEncoder::class => DI\autowire()
            ->constructorParameter("key", DI\get("authentication.jwt.key")),

        \DateTimeZone::class => DI\autowire()
            ->constructorParameter("timezone", DI\get("timezone")),

        UserRepositoryInterface::class => DI\get(MariaDbUserRepository::class),
        PermissionRepositoryInterface::class => DI\get(MariaDbPermissionRepository::class),
        SectorRepositoryInterface::class => DI\get(MariaDbSectorRepository::class),
        UserSectorPermissionRepositoryInterface::class => DI\get(MariaDbUserSectorPermissionRepository::class),
        GameRepositoryInterface::class => DI\get(MariaDbGameRepository::class),
        GenreRepositoryInterface::class => DI\get(MariaDbGenreRepository::class),
        PlatformRepositoryInterface::class => DI\get(MariaDbPlatformRepository::class),
        GameGenreRepositoryInterface::class => DI\get(MariaDbGameGenreRepository::class),
        GamePlatformRepositoryInterface::class => DI\get(MariaDbGamePlatformRepository::class),

        AuthenticationTokenEncoder::class => DI\get(JwtAuthenticationTokenEncoder::class),
        AuthenticationTokenDecoder::class => DI\get(JwtAuthenticationTokenDecoder::class),
        EncodedAuthenticationTokenValidator::class => DI\get(JwtEncodedAuthenticationTokenValidator::class),
        DecodedAuthenticationTokenValidator::class => DI\get(JwtDecodedAuthenticationTokenValidator::class),

        TokenCacheInterface::class => DI\get(PredisTokenCache::class),

        \PDO::class => DI\factory(function (ContainerInterface $container) {
            $adapter = $container->get("repository.adapter");
            $host = $container->get("repository.host");
            $database = $container->get("repository.database");
            $username = $container->get("repository.username");
            $password = $container->get("repository.password");
            $dsn = "$adapter:host=$host;dbname=$database;";
            return new \PDO(
                $dsn,
                $username,
                $password,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
        }),

        Client::class => DI\factory(function (ContainerInterface $container) {
            $scheme = $container->get("cache.redis.scheme");
            $host = $container->get("cache.redis.host");
            $port = $container->get("cache.redis.port");
            return new Client([
                "scheme" => $scheme,
                "host" => $host,
                "port" => $port,
            ]);
        })
    ];

} catch (\Throwable $e) {
    throw $e;
}
