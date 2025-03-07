<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\GameGenreService;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\RedisUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\SodiumEncryption;
use Mvreisg\GamebaseBackend\Presentation\Controllers\GameGenreController;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGameGenreRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;

class GameGenreControllerFactory
{
    public static function get(): GameGenreController
    {
        $gameGenreRepository = new MariaDBGameGenreRepository(MariaDBConnection::get());
        $gameGenreService = new GameGenreService($gameGenreRepository);
        $userRepository = new MariaDBUserRepository(MariaDBConnection::get());
        $encrypter = new SodiumEncryption();
        $userCache = new RedisUserCache(RedisConnection::get());
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $controller = new GameGenreController($gameGenreService, $authService);
        return $controller;
    }
}
