<?php
    namespace Gamebase\Presentation\Routers;

    use Gamebase\Infrastructure\Utils\Pathfinder;
    use Gamebase\Presentation\Factories\GameControllerFactory;
    use Gamebase\Presentation\Http\HttpRequest;
    use Gamebase\Presentation\Http\HttpResponse;
    use Gamebase\Presentation\Http\HttpApplication;

    include_once(PATHFINDER_DIRECTORY);
	include_once(Pathfinder::find("src/presentation/factories/GameControllerFactory.php"));    

    class GameRouter 
    {
        public function register(HttpApplication $app) {        
            $gameController = GameControllerFactory::get();
    
            $app->add("POST", "/game", function(HttpRequest $request, HttpResponse $response) use ($gameController) 
            {
                $gameController->insert($request, $response);
            });

            $app->add("GET", "/game", function(HttpRequest $request, HttpResponse $response) use ($gameController) 
            {
                $gameController->findAll($request, $response);
            });
            
            $app->add("GET", "/game/:gameId", function(HttpRequest $request, HttpResponse $response) use ($gameController) 
            {
                $gameController->findById($request, $response);
            });

            $app->add("PUT", "/game/:gameId", function(HttpRequest $request, HttpResponse $response) use ($gameController) 
            {
                $gameController->edit($request, $response);
            });
        }
    }    
?>