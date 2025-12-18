<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\SectorPermissionService;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Entities\JwtTokenAuthenticationClock;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\JwtTokenAuthentication;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\RedisUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpSectorPermissionController;

class HttpSectorPermissionControllerFactory
{
    public static function make(): HttpSectorPermissionController
    {
        try {
            $repositoryConnection = MariaDBConnection::get();

            $sectorRepository = new MariaDBSectorRepository(
                $repositoryConnection
            );

            $permissionRepository = new MariaDBPermissionRepository(
                $repositoryConnection
            );

            $sectorPermissionRepository = new MariaDBSectorPermissionRepository(
                $repositoryConnection
            );

            $sectorPermissionService = new SectorPermissionService(
                $sectorRepository,
                $permissionRepository,
                $sectorPermissionRepository
            );

            $userRepository = new MariaDBUserRepository(
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

            $controller = new HttpSectorPermissionController(
                $sectorPermissionService,
                $authService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
