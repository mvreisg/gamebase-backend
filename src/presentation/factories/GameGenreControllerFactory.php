<?php
    namespace Gamebase\Presentation\Factories;

    use Gamebase\Application\Services\GameGenreService;
    use Gamebase\Presentation\Controllers\GameGenreController;
    use Gamebase\Infrastructure\Database\MariaDBConnection;
    use Gamebase\Infrastructure\Repositories\MariaDBGameGenreRepository;
    
	include_once("./../src/presentation/controllers/GameGenreController.php");
    include_once("./../src/application/service/GameGenreService.php");
    include_once("./../src/infrastructure/database/MariaDBConnection.php");
    include_once("./../src/infrastructure/repositories/MariaDBGameGenreRepository.php");

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