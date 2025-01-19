<?php
    namespace Gamebase\Presentation\Routers;

    use Gamebase\Application\Services\PlatformService;
    use Gamebase\Infrastructure\Database\MariaDBConnection;
    use Gamebase\Infrastructure\Repositories\MariaDBPlatformRepository;
    use Gamebase\Presentation\Controllers\PlatformController;
    
	include_once("./../src/presentation/controllers/PlatformController.php");
    include_once("./../src/application/service/PlatformService.php");
    include_once("./../src/infrastructure/database/MariaDBConnection.php");
    include_once("./../src/infrastructure/repositories/MariaDBPlatformRepository.php");

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