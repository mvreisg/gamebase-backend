<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\GameService;
use Mvreisg\GamebaseBackend\Presentation\Controllers\GameController;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBGameRepository;

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
        $repository = new MariaDBGameRepository(MariaDBConnection::get());
        $service = new GameService($repository);
        $controller = new GameController($service);
        return $controller;
    }
}
