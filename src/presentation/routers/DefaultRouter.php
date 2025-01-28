<?php
    namespace Mvreisg\GamebaseBackend\Presentation\Routers;

    use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
    use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
    use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpApplication;

    class DefaultRouter 
    {
        public function register(HttpApplication $app) {        
            $app->add("GET", "/", function(HttpRequest $request, HttpResponse $response)
            {
                $response->appendString("Servidor funcionando!")->status(HTTP_STATUS_CODE_200)->send();
            });

            $app->add("GET", NON_EXISTANT_ROUTE, function(HttpRequest $request, HttpResponse $response)
            {
                $response->appendString("Rota não encontrada!")->status(HTTP_STATUS_CODE_404)->send();
            });
        }
    }    
?>