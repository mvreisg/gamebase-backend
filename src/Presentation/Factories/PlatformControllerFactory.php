<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\PlatformService;
use Mvreisg\GamebaseBackend\Infrastructure\Database\MariaDBConnection;
use Mvreisg\GamebaseBackend\Presentation\Controllers\PlatformController;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBPlatformRepository;

/**
 * Platform controller factory class.
 */
class PlatformControllerFactory
{
    /**
     * Static method that returns a instance of PlatformController.
     * @return PlatformController The instance.
     */
    public static function get(): PlatformController
    {
        $repository = new MariaDBPlatformRepository(MariaDBConnection::get());
        $service = new PlatformService($repository);
        $controller = new PlatformController($service);
        return $controller;
    }
}
