<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\PlatformService;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\RedisUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\DefuseEncryption;
use Mvreisg\GamebaseBackend\Presentation\Controllers\PlatformController;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPlatformRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;

class PlatformControllerFactory
{
    public static function get(): PlatformController
    {
        $platformRepository = new MariaDBPlatformRepository(MariaDBConnection::get());
        $platformService = new PlatformService($platformRepository);
        $userRepository = new MariaDBUserRepository(MariaDBConnection::get());
        $encrypter = new DefuseEncryption();
        $userCache = new RedisUserCache(RedisConnection::get());
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $controller = new PlatformController($platformService, $authService);
        return $controller;
    }
}
