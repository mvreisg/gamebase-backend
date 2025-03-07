<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\GenreService;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\RedisUserCache;
use Mvreisg\GamebaseBackend\Presentation\Controllers\GenreController;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\SodiumEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGenreRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;

class GenreControllerFactory
{
    public static function get(): GenreController
    {
        $genreRepository = new MariaDBGenreRepository(MariaDBConnection::get());
        $genreService = new GenreService($genreRepository);
        $userRepository = new MariaDBUserRepository(MariaDBConnection::get());
        $encrypter = new SodiumEncryption();
        $userCache = new RedisUserCache(RedisConnection::get());
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $controller = new GenreController($genreService, $authService);
        return $controller;
    }
}
