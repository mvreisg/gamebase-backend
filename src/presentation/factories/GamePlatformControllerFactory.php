<?php
    namespace Gamebase\Presentation\Factories;

    use Gamebase\Application\Services\GamePlatformService;
    use Gamebase\Presentation\Controllers\GamePlatformController;
    use Gamebase\Infrastructure\Database\MariaDBConnection;
    use Gamebase\Infrastructure\Persistance\MariaDBGamePlatformRepository;
    use Gamebase\Infrastructure\Utils\Pathfinder;

    include_once(PATHFINDER_DIRECTORY);
	include_once(Pathfinder::find("src/presentation/controllers/GamePlatformController.php"));
    include_once(Pathfinder::find("src/application/service/GamePlatformService.php"));
    include_once(Pathfinder::find("src/infrastructure/database/MariaDBConnection.php"));
    include_once(Pathfinder::find("src/infrastructure/persistance/MariaDBGamePlatformRepository.php"));

    class GamePlatformControllerFactory 
    {
        public static function get(): GamePlatformController {        
            $repository = new MariaDBGamePlatformRepository(MariaDBConnection::get());
            $service = new GamePlatformService($repository);
            $controller = new GamePlatformController($service);
            return $controller;
        }
    }    
?>