<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\UserPermission\UserPermissionService;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\JwtTokenAuthentication;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\RedisUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserPermissionRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpUserPermissionController;

class HttpUserPermissionControllerFactory
{
    public static function make(): HttpUserPermissionController
    {
        $repositoryConnection = MariaDBConnection::get();

        $userRepository = new MariaDBUserRepository(
            $repositoryConnection
        );

        $permissionRepository = new MariaDBPermissionRepository(
            $repositoryConnection
        );

        $userPermissionRepository = new MariaDBUserPermissionRepository(
            $repositoryConnection
        );

        $userPermissionService = new UserPermissionService(
            $userRepository,
            $permissionRepository,
            $userPermissionRepository
        );

        $userRepository = new MariaDBUserRepository(
            $repositoryConnection
        );

        $encrypter = new DefuseEncryption();

        $cacheConnection = RedisConnection::get();

        $userCache = new RedisUserCache(
            $cacheConnection
        );

        $authenticator = new JwtTokenAuthentication();

        $authService = new AuthenticationService(
            $userRepository,
            $encrypter,
            $userCache,
            $authenticator
        );

        $controller = new HttpUserPermissionController(
            $userPermissionService,
            $authService
        );

        return $controller;
    }
}
