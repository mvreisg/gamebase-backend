<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\GamePlatformService;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Presentation\Controllers\GamePlatformController;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBGamePlatformRepository;

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
        $repository = new MariaDBGamePlatformRepository(MariaDBConnection::get());
        $service = new GamePlatformService($repository);
        $controller = new GamePlatformController($service);
        return $controller;
    }
}
