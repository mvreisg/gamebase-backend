<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\GameService;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\RedisUserCache;
use Mvreisg\GamebaseBackend\Presentation\Controllers\GameController;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\SodiumEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBGameRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBUserRepository;

/**
 * Game controller factory class.
 */
class GameControllerFactory
{
    /**
     * Static method with the factory pattern to return the game controller.
     * @return GameController The instance.
     */
    public static function get(): GameController
    {
        $gameRepository = new MariaDBGameRepository(MariaDBConnection::get());
        $userRepository = new MariaDBUserRepository(MariaDBConnection::get());
        $gameService = new GameService($gameRepository);
        $encrypter = new SodiumEncryption();
        $userCache = new RedisUserCache(RedisConnection::get());
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $controller = new GameController($gameService, $authService);
        return $controller;
    }
}
