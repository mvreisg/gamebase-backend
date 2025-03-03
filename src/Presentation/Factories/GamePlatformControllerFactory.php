<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatformService;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\RedisUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\SodiumEncryption;
use Mvreisg\GamebaseBackend\Presentation\Controllers\GamePlatformController;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBGamePlatformRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBUserRepository;

/**
 * Game Platform controller factory class.
 */
class GamePlatformControllerFactory
{
    /**
     * Static method that returns an instance of Game Platform controller.
     * @return GamePlatformController The instance.
     */
    public static function get(): GamePlatformController
    {
        $gamePlatformRepository = new MariaDBGamePlatformRepository(MariaDBConnection::get());
        $gamePlatformService = new GamePlatformService($gamePlatformRepository);
        $userRepository = new MariaDBUserRepository(MariaDBConnection::get());
        $encrypter = new SodiumEncryption();
        $userCache = new RedisUserCache(RedisConnection::get());
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $controller = new GamePlatformController($gamePlatformService, $authService);
        return $controller;
    }
}
