<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Sector\SectorService;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\JwtTokenAuthentication;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\RedisUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpSectorController;

class HttpSectorControllerFactory
{
    public static function make(): HttpSectorController
    {
        $repositoryConnection = MariaDBConnection::get();

        $sectorRepository = new MariaDBSectorRepository(
            $repositoryConnection
        );

        $sectorService = new SectorService(
            $sectorRepository
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

        $controller = new HttpSectorController(
            $sectorService,
            $authService
        );

        return $controller;
    }
}
