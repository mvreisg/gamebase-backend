<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\UserService;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\RedisUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Controllers\UserController;

class UserControllerFactory
{
    public static function get(): UserController
    {
        $repository = new MariaDBUserRepository(MariaDBConnection::get());
        $encrypter = new DefuseEncryption();
        $service = new UserService($repository, $encrypter);
        $encrypter = new DefuseEncryption();
        $cache = new RedisUserCache(RedisConnection::get());
        $authService = new AuthenticationService($repository, $encrypter, $cache);
        $controller = new UserController($service, $authService);
        return $controller;
    }
}
