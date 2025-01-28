<?php
    namespace Mvreisg\GamebaseBackend\Presentation\Routers;
    
    use Mvreisg\GamebaseBackend\Presentation\Factories\PlatformControllerFactory;
    use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
    use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
    use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpApplication;

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