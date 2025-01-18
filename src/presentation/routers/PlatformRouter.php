<?php
    namespace Gamebase\Presentation\Routers;

    use Gamebase\Infrastructure\Utils\Pathfinder;
    use Gamebase\Presentation\Http\HttpRequest;
    use Gamebase\Presentation\Http\HttpResponse;
    use Gamebase\Presentation\Http\HttpApplication;

    include_once(PATHFINDER_DIRECTORY);
	include_once(Pathfinder::find("src/presentation/factories/PlatformControllerFactory.php"));

    class PlatformRouter 
    {
        public function register(HttpApplication $app) {        
            $controller = PlatformControllerFactory::get();
    
            $app->add("POST", "/platform", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->insert($request, $response);
            });

            $app->add("GET", "/platform", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->findAll($request, $response);
            });

            $app->add("GET", "/platform/:platformId", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->findById($request, $response);
            });

            $app->add("PUT", "/platform/:platformId", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->edit($request, $response);
            });
        }
    }    
?>