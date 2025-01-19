<?php
    namespace Gamebase\Presentation\Routers;

    use Gamebase\Presentation\Factories\GamePlatformControllerFactory;
    use Gamebase\Infrastructure\Http\HttpRequest;
    use Gamebase\Infrastructure\Http\HttpResponse;
    use Gamebase\Infrastructure\Http\HttpApplication;
    
	include_once("./../src/presentation/factories/GamePlatformControllerFactory.php");    

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