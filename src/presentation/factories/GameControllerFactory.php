<?php
namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\GameService;
use Mvreisg\GamebaseBackend\Presentation\Controllers\GameController;
use Mvreisg\GamebaseBackend\Infrastructure\Database\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBGameRepository;
    
class GameControllerFactory
{
    public static function get(): GameController
    {
        $repository = new MariaDBGameRepository(MariaDBConnection::get());
        $service = new GameService($repository);
        $controller = new GameController($service);
        return $controller;
    }
}
