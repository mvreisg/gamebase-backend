<?php

namespace Mvreisg\GamebaseBackend\Presentation\Routers;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpApplication;

class DefaultRouter
{
    public function register(HttpApplication $app)
    {
        $app->add('*', '/', function (HttpRequest $request, HttpResponse $response) {
            $response
                ->appendString('Servidor funcionando!')
                ->status(HttpApplication::STATUS_CODES[200])
                ->send();
        });

        $app->add('*', HttpApplication::NON_EXISTANT_ROUTE, function (HttpRequest $request, HttpResponse $response) {
            $response
                ->appendString('Rota não encontrada!')
                ->status(HttpApplication::STATUS_CODES[404])
                ->send();
        });
    }
}
