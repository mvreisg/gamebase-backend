<?php
    namespace Gamebase\Presentation\Routers;

    use Gamebase\Infrastructure\Utils\Pathfinder;
    use Gamebase\Presentation\Factories\GamePlatformControllerFactory;
    use Gamebase\Presentation\Http\HttpRequest;
    use Gamebase\Presentation\Http\HttpResponse;
    use Gamebase\Presentation\Http\HttpApplication;

    include_once(PATHFINDER_DIRECTORY);
	include_once(Pathfinder::find("src/presentation/factories/GamePlatformControllerFactory.php"));    

    class GamePlatformRouter 
    {
        public function register(HttpApplication $app) {        
            $controller = GamePlatformControllerFactory::get();
    
            $app->add("POST", "/game/:gameId/platform", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->insert($request, $response);
            });

            $app->add("PUT", "/game/:gameId/platform", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->edit($request, $response);
            });

            $app->add("GET", "/game/:gameId/platform", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->findAllPlatformsIdsByGameId($request, $response);
            });

            $app->add("DELETE", "/game/:gameId/platform", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->deleteAllPlatformsByGameId($request, $response);
            });
        }
    }    
?>