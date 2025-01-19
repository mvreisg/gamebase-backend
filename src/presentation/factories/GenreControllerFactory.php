<?php
    namespace Gamebase\Presentation\Routers;

    use Gamebase\Application\Services\GenreService;
    use Gamebase\Infrastructure\Database\MariaDBConnection;
    use Gamebase\Infrastructure\Repositories\MariaDBGenreRepository;
    use Gamebase\Presentation\Controllers\GenreController;
    
	include_once("./../src/presentation/controllers/GenreController.php");
    include_once("./../src/application/service/GenreService.php");
    include_once("./../src/infrastructure/database/MariaDBConnection.php");
    include_once("./../src/infrastructure/repositories/MariaDBGenreRepository.php");

    class GenreControllerFactory 
    {
        public static function get(): GenreController {        
            $repository = new MariaDBGenreRepository(MariaDBConnection::get());
            $service = new GenreService($repository);
            $controller = new GenreController($service);
            return $controller;
        }
    }    
?>