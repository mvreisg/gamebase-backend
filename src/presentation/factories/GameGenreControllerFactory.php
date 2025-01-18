<?php
    namespace Gamebase\Presentation\Factories;

    use Gamebase\Application\Services\GameGenreService;
    use Gamebase\Presentation\Controllers\GameGenreController;
    use Gamebase\Infrastructure\Database\MariaDBConnection;
    use Gamebase\Infrastructure\Persistance\MariaDBGameGenreRepository;
    use Gamebase\Infrastructure\Utils\Pathfinder;

    include_once(PATHFINDER_DIRECTORY);
	include_once(Pathfinder::find("src/presentation/controllers/GameGenreController.php"));
    include_once(Pathfinder::find("src/application/service/GameGenreService.php"));
    include_once(Pathfinder::find("src/infrastructure/database/MariaDBConnection.php"));
    include_once(Pathfinder::find("src/infrastructure/persistance/MariaDBGameGenreRepository.php"));

    class GameGenreControllerFactory 
    {
        public static function get(): GameGenreController {        
            $repository = new MariaDBGameGenreRepository(MariaDBConnection::get());
            $service = new GameGenreService($repository);
            $controller = new GameGenreController($service);
            return $controller;
        }
    }    
?>