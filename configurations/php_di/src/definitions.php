<?php

declare(strict_types=1);

use DI\Container;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\AuthenticationTokenProvider;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Game\Repository\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Repository\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Repository\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Genre\Repository\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Permission\Repository\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Platform\Repository\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Sector\Repository\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Cache\Predis\PredisAuthenticationTokenCache;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Provider\JwtTokenProvider;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Provider\Option\JwtTokenProviderOptions;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\Option\DefuseEncryptionOptions;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Sodium\Option\SodiumEncryptionOptions;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbGameGenreRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbGamePlatformRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbGameRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbGenreRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbPlatformRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbSectorRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbUserRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\MariaDbUserSectorPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Time\Clock;
use Psr\Container\ContainerInterface;
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

        ClockInterface::class => DI\get(Clock::class),

        EncryptionInterface::class => DI\get(DefuseEncryption::class),

        SodiumEncryptionOptions::class => DI\autowire()
            ->constructorParameter("key", DI\get("encryption.sodium.key")),

        DefuseEncryptionOptions::class => DI\autowire()
            ->constructorParameter("key", DI\get("encryption.defuse.key")),

        JwtTokenProviderOptions::class => DI\autowire()
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

        AuthenticationTokenProvider::class => DI\get(JwtTokenProvider::class),

        AuthenticationTokenCacheInterface::class => DI\get(PredisAuthenticationTokenCache::class),

        \PDO::class => DI\factory(function (Container $container) {
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
