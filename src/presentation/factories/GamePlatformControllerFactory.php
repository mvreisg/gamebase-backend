<?php
namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\GamePlatformService;
use Mvreisg\GamebaseBackend\Infrastructure\Database\MariaDBConnection;
use Mvreisg\GamebaseBackend\Presentation\Controllers\GamePlatformController;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBGamePlatformRepository;
    
class GamePlatformControllerFactory
{
    public static function get(): GamePlatformController
    {
        $repository = new MariaDBGamePlatformRepository(MariaDBConnection::get());
        $service = new GamePlatformService($repository);
        $controller = new GamePlatformController($service);
        return $controller;
    }
}
