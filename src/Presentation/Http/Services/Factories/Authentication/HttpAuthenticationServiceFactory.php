<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Clock\JwtAuthenticationTokenClock;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Decoder\JwtAuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Encoder\JwtAuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Validator\Decoded\JwtDecodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Validator\Encoded\JwtEncodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\Connection\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\Token\RedisTokenCache;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;

class HttpAuthenticationServiceFactory
{
    public static function make(\PDO $repositoryConnection, EncryptionInterface $encrypter): AuthenticationService
    {
        $userRepository = new MariaDBUserRepository(
            $repositoryConnection
        );

        $permissionRepository = new MariaDBPermissionRepository(
            $repositoryConnection
        );

        $sectorRepository = new MariaDBSectorRepository(
            $repositoryConnection
        );

        $userPermissionRepository = new MariaDBUserPermissionRepository(
            $repositoryConnection
        );

        $sectorPermissionRepository = new MariaDBSectorPermissionRepository(
            $repositoryConnection
        );

        $cacheConnection = RedisConnection::get();

        $tokenCache = new RedisTokenCache(
            $cacheConnection
        );

        $jwtAuthenticationTokenClock = new JwtAuthenticationTokenClock();

        $authenticationTokenEncoder = new JwtAuthenticationTokenEncoder(
            $jwtAuthenticationTokenClock
        );

        $authenticationTokenDecoder = new JwtAuthenticationTokenDecoder(
            $jwtAuthenticationTokenClock
        );

        $decodedAuthenticationTokenValidator = new JwtDecodedAuthenticationTokenValidator(
            $jwtAuthenticationTokenClock
        );

        $encodedAuthenticationTokenValidator = new JwtEncodedAuthenticationTokenValidator(
            $authenticationTokenDecoder,
            $decodedAuthenticationTokenValidator
        );

        $service = new AuthenticationService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $permissionRepository,
            $sectorRepository,
            $sectorPermissionRepository,
            $userPermissionRepository,
            $encodedAuthenticationTokenValidator
        );

        return $service;
    }
}
