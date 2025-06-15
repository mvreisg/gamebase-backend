<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\SectorService;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\RedisUserCache;
use Mvreisg\GamebaseBackend\Presentation\Controllers\SectorController;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;

class SectorControllerFactory
{
    public static function make(): SectorController
    {
        $sectorRepository = new MariaDBSectorRepository(MariaDBConnection::get());
        $sectorService = new SectorService($sectorRepository);
        $userRepository = new MariaDBUserRepository(MariaDBConnection::get());
        $encrypter = new DefuseEncryption();
        $userCache = new RedisUserCache(RedisConnection::get());
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $controller = new SectorController($sectorService, $authService);
        return $controller;
    }
}
