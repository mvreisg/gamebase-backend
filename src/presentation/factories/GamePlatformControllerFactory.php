<?php
    namespace Gamebase\Presentation\Factories;

    use Gamebase\Application\Services\GamePlatformService;
    use Gamebase\Presentation\Controllers\GamePlatformController;
    use Gamebase\Infrastructure\Database\MariaDBConnection;
    use Gamebase\Infrastructure\Repositories\MariaDBGamePlatformRepository;
    
	include_once("./../src/presentation/controllers/GamePlatformController.php");
    include_once("./../src/application/service/GamePlatformService.php");
    include_once("./../src/infrastructure/database/MariaDBConnection.php");
    include_once("./../src/infrastructure/repositories/MariaDBGamePlatformRepository.php");

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