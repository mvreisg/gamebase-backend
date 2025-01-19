<?php
    namespace Gamebase\Presentation\Routers;

    use Gamebase\Presentation\Factories\GameControllerFactory;
    use Gamebase\Infrastructure\Http\HttpRequest;
    use Gamebase\Infrastructure\Http\HttpResponse;
    use Gamebase\Infrastructure\Http\HttpApplication;
    
	include_once("./../src/presentation/factories/GameControllerFactory.php");    

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