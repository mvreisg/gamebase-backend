<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatformService;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\RedisUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\DefuseEncryption;
use Mvreisg\GamebaseBackend\Presentation\Controllers\GamePlatformController;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGamePlatformRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;

class GamePlatformControllerFactory
{
    public static function make(): GamePlatformController
    {
        $gamePlatformRepository = new MariaDBGamePlatformRepository(MariaDBConnection::get());
        $gamePlatformService = new GamePlatformService($gamePlatformRepository);
        $userRepository = new MariaDBUserRepository(MariaDBConnection::get());
        $encrypter = new DefuseEncryption();
        $userCache = new RedisUserCache(RedisConnection::get());
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $controller = new GamePlatformController($gamePlatformService, $authService);
        return $controller;
    }
}
