<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\UserService;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\RedisUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\SodiumEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Controllers\UserController;

class UserControllerFactory
{
    public static function get(): UserController
    {
        $repository = new MariaDBUserRepository(MariaDBConnection::get());
        $encrypter = new SodiumEncryption();
        $service = new UserService($repository, $encrypter);
        $encrypter = new SodiumEncryption();
        $cache = new RedisUserCache(RedisConnection::get());
        $authService = new AuthenticationService($repository, $encrypter, $cache);
        $controller = new UserController($service, $authService);
        return $controller;
    }
}
