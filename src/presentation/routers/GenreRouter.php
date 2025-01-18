<?php
    namespace Gamebase\Presentation\Routers;

    use Gamebase\Infrastructure\Utils\Pathfinder;
    use Gamebase\Presentation\Http\HttpRequest;
    use Gamebase\Presentation\Http\HttpResponse;
    use Gamebase\Presentation\Http\HttpApplication;

    include_once(PATHFINDER_DIRECTORY);
	include_once(Pathfinder::find("src/presentation/factories/GenreControllerFactory.php"));

    class GenreRouter 
    {
        public function register(HttpApplication $app) {        
            $controller = GenreControllerFactory::get();
    
            $app->add("POST", "/genre", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->insert($request, $response);
            });

            $app->add("GET", "/genre", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->findAll($request, $response);
            });

            $app->add("GET", "/genre/:genreId", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->findById($request, $response);
            });

            $app->add("PUT", "/genre/:genreId", function(HttpRequest $request, HttpResponse $response) use ($controller) 
            {
                $controller->edit($request, $response);
            });
        }
    }    
?>