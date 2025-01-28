<?php
    namespace Mvreisg\GamebaseBackend\Presentation\Factories;

    use Mvreisg\GamebaseBackend\Application\Services\PlatformService;
    use Mvreisg\GamebaseBackend\Infrastructure\Database\MariaDBConnection;
    use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBPlatformRepository;
    use Mvreisg\GamebaseBackend\Presentation\Controllers\PlatformController;
    
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