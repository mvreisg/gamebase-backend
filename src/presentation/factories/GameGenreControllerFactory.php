<?php
namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\GameGenreService;
use Mvreisg\GamebaseBackend\Infrastructure\Database\MariaDBConnection;
use Mvreisg\GamebaseBackend\Presentation\Controllers\GameGenreController;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBGameGenreRepository;

class GameGenreControllerFactory
{
    public static function get(): GameGenreController
    {
        $repository = new MariaDBGameGenreRepository(MariaDBConnection::get());
        $service = new GameGenreService($repository);
        $controller = new GameGenreController($service);
        return $controller;
    }
}
