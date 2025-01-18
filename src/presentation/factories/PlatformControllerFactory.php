<?php
    namespace Gamebase\Presentation\Routers;

    use Gamebase\Application\Services\PlatformService;
    use Gamebase\Infrastructure\Database\MariaDBConnection;
    use Gamebase\Infrastructure\Persistance\MariaDBPlatformRepository;
    use Gamebase\Infrastructure\Utils\Pathfinder;
    use Gamebase\Presentation\Controllers\PlatformController;

    include_once(PATHFINDER_DIRECTORY);
	include_once(Pathfinder::find("src/presentation/controllers/PlatformController.php"));
    include_once(Pathfinder::find("src/application/service/PlatformService.php"));
    include_once(Pathfinder::find("src/infrastructure/database/MariaDBConnection.php"));
    include_once(Pathfinder::find("src/infrastructure/persistance/MariaDBPlatformRepository.php"));

    class PlatformControllerFactory 
    {
        public static function get(): PlatformController {        
            $repository = new MariaDBPlatformRepository(MariaDBConnection::get());
            $service = new PlatformService($repository);
            $controller = new PlatformController($service);
            return $controller;
        }
    }    
?>