<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Genre\GenreService;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Entities\JwtTokenAuthenticationClock;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\JwtTokenAuthentication;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\RedisUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGenreRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpGenreController;

class HttpGenreControllerFactory
{
    public static function make(): HttpGenreController
    {
        try {
            $repositoryConnection = MariaDBConnection::get();

            $genreEntityRepository = new MariaDBGenreRepository(
                $repositoryConnection
            );

            $genreService = new GenreService(
                $genreEntityRepository
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

            $controller = new HttpGenreController(
                $genreService,
                $authService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
