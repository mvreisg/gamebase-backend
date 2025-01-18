<?php
    namespace Gamebase\Presentation\Routers;

    use Gamebase\Infrastructure\Utils\Pathfinder;
    use Gamebase\Presentation\Factories\GameGenreControllerFactory;
    use Gamebase\Presentation\Http\HttpRequest;
    use Gamebase\Presentation\Http\HttpResponse;
    use Gamebase\Presentation\Http\HttpApplication;

    include_once(PATHFINDER_DIRECTORY);
	include_once(Pathfinder::find("src/presentation/factories/GameGenreControllerFactory.php"));    

    class GameGenreRouter 
    {
        public function register(HttpApplication $app) {        
            $controller = GameGenreControllerFactory::get();
    
            $app->add("POST", "/game/:gameId/genre", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->insert($request, $response);
            });

            $app->add("PUT", "/game/:gameId/genre", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->edit($request, $response);
            });

            $app->add("GET", "/game/:gameId/genre", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->findAllGenresIdByGameId($request, $response);
            });

            $app->add("DELETE", "/game/:gameId/genre", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->deleteAllGenresByGameId($request, $response);
            });
        }
    }    
?>