<?php
    namespace Gamebase\Presentation\Routers;

    use Gamebase\Infrastructure\Http\HttpRequest;
    use Gamebase\Infrastructure\Http\HttpResponse;
    use Gamebase\Infrastructure\Http\HttpApplication;

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