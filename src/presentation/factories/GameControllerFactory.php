<?php
    namespace Gamebase\Presentation\Factories;

    use Gamebase\Application\Services\GameService;
    use Gamebase\Presentation\Controllers\GameController;
    use Gamebase\Infrastructure\Database\MariaDBConnection;
    use Gamebase\Infrastructure\Repositories\MariaDBGameRepository;
    
	include_once("./../src/presentation/controllers/GameController.php");
    include_once("./../src/application/service/GameService.php");
    include_once("./../src/infrastructure/database/MariaDBConnection.php");
    include_once("./../src/infrastructure/repositories/MariaDBGameRepository.php");

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