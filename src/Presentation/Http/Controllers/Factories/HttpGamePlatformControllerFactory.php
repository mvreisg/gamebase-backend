<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\GamePlatformService;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Entities\JwtTokenAuthenticationClock;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\JwtTokenAuthentication;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\RedisUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGamePlatformRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGameRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPlatformRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpGamePlatformController;

class HttpGamePlatformControllerFactory
{
    public static function make(): HttpGamePlatformController
    {
        try {
            $repositoryConnection = MariaDBConnection::get();

            $gameRepository = new MariaDBGameRepository(
                $repositoryConnection
            );

            $platformRepository = new MariaDBPlatformRepository(
                $repositoryConnection
            );

            $gamePlatformEntityRepository = new MariaDBGamePlatformRepository(
                $repositoryConnection
            );

            $gamePlatformService = new GamePlatformService(
                $gameRepository,
                $platformRepository,
                $gamePlatformEntityRepository
            );

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

            $encrypter = new DefuseEncryption();

            $cacheConnection = RedisConnection::get();

            $userCache = new RedisUserCache(
                $cacheConnection
            );

            $authenticationClock = new JwtTokenAuthenticationClock();

            $authenticator = new JwtTokenAuthentication(
                $authenticationClock
            );

            $authService = new AuthenticationService(
                $userRepository,
                $permissionRepository,
                $sectorRepository,
                $userPermissionRepository,
                $sectorPermissionRepository,
                $encrypter,
                $userCache,
                $authenticator,
                $authenticationClock
            );

            $controller = new HttpGamePlatformController(
                $gamePlatformService,
                $authService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
