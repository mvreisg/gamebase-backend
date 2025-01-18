<?php
    namespace Gamebase\Presentation\Routers;

    use Gamebase\Application\Services\GenreService;
    use Gamebase\Infrastructure\Database\MariaDBConnection;
    use Gamebase\Infrastructure\Persistance\MariaDBGenreRepository;
    use Gamebase\Infrastructure\Utils\Pathfinder;
    use Gamebase\Presentation\Controllers\GenreController;

    include_once(PATHFINDER_DIRECTORY);
	include_once(Pathfinder::find("src/presentation/controllers/GenreController.php"));
    include_once(Pathfinder::find("src/application/service/GenreService.php"));
    include_once(Pathfinder::find("src/infrastructure/database/MariaDBConnection.php"));
    include_once(Pathfinder::find("src/infrastructure/persistance/MariaDBGenreRepository.php"));

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