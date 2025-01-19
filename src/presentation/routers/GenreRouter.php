<?php
    namespace Gamebase\Presentation\Routers;
    
    use Gamebase\Infrastructure\Http\HttpRequest;
    use Gamebase\Infrastructure\Http\HttpResponse;
    use Gamebase\Infrastructure\Http\HttpApplication;
    
	include_once("./../src/presentation/factories/GenreControllerFactory.php");

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