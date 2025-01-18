<?php
    namespace Gamebase\Presentation\Factories;

    use Gamebase\Application\Services\GameService;
    use Gamebase\Presentation\Controllers\GameController;
    use Gamebase\Infrastructure\Database\MariaDBConnection;
    use Gamebase\Infrastructure\Persistance\MariaDBGameRepository;
    use Gamebase\Infrastructure\Utils\Pathfinder;

    include_once(PATHFINDER_DIRECTORY);
	include_once(Pathfinder::find("src/presentation/controllers/GameController.php"));
    include_once(Pathfinder::find("src/application/service/GameService.php"));
    include_once(Pathfinder::find("src/infrastructure/database/MariaDBConnection.php"));
    include_once(Pathfinder::find("src/infrastructure/persistance/MariaDBGameRepository.php"));

    class GameControllerFactory 
    {
        public static function get(): GameController {        
            $repository = new MariaDBGameRepository(MariaDBConnection::get());
            $service = new GameService($repository);
            $gameController = new GameController($service);
            return $gameController;
        }
    }    
?>