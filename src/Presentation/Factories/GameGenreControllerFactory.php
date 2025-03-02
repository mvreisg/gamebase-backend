<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\GameGenreService;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Presentation\Controllers\GameGenreController;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBGameGenreRepository;

/**
 * Game Genre controller factory class.
 */
class GameGenreControllerFactory
{
    /**
     * Static method that get the Game Genre controller instance.
     * @return GameGenreController The instance.
     */
    public static function get(): GameGenreController
    {
        $repository = new MariaDBGameGenreRepository(MariaDBConnection::get());
        $service = new GameGenreService($repository);
        $controller = new GameGenreController($service);
        return $controller;
    }
}
