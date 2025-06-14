<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\GameService;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\RedisUserCache;
use Mvreisg\GamebaseBackend\Presentation\Controllers\GameController;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGameRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;

class GameControllerFactory
{
    public static function make(): GameController
    {
        $gameRepository = new MariaDBGameRepository(MariaDBConnection::get());
        $gameService = new GameService($gameRepository);
        $userRepository = new MariaDBUserRepository(MariaDBConnection::get());
        $encrypter = new DefuseEncryption();
        $userCache = new RedisUserCache(RedisConnection::get());
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $controller = new GameController($gameService, $authService);
        return $controller;
    }
}
