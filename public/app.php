<?php

use Mvreisg\GamebaseBackend\Presentation\Http\Router\Factories\HttpRouterFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Routes\HttpAuthenticationRoutes;
use Mvreisg\GamebaseBackend\Presentation\Http\Routes\HttpGameGenreRoutes;
use Mvreisg\GamebaseBackend\Presentation\Http\Routes\HttpGamePlatformRoutes;
use Mvreisg\GamebaseBackend\Presentation\Http\Routes\HttpGameRoutes;
use Mvreisg\GamebaseBackend\Presentation\Http\Routes\HttpGenreRoutes;
use Mvreisg\GamebaseBackend\Presentation\Http\Routes\HttpPermissionRoutes;
use Mvreisg\GamebaseBackend\Presentation\Http\Routes\HttpPlatformRoutes;
use Mvreisg\GamebaseBackend\Presentation\Http\Routes\HttpSectorPermissionRoutes;
use Mvreisg\GamebaseBackend\Presentation\Http\Routes\HttpSectorRoutes;
use Mvreisg\GamebaseBackend\Presentation\Http\Routes\HttpUserPermissionRoutes;
use Mvreisg\GamebaseBackend\Presentation\Http\Routes\HttpUserRoutes;

try {
    require_once dirname(__DIR__) . "/bootstrap.php";

    HttpRouterFactory::make()
        ->addRoutes(
            HttpAuthenticationRoutes::get()
        )
        ->addRoutes(
            HttpGameRoutes::get()
        )
        /*
        ->addRoutes(
            HttpGameGenreRoutes::get()
        )
        ->addRoutes(
            HttpGamePlatformRoutes::get()
        )
        */
        ->addRoutes(
            HttpGenreRoutes::get()
        )/*
        ->addRoutes(
            HttpPermissionRoutes::get()
        )*/
        ->addRoutes(
            HttpPlatformRoutes::get()
        )/*
        ->addRoutes(
            HttpSectorPermissionRoutes::get()
        )
        ->addRoutes(
            HttpSectorRoutes::get()
        )
        ->addRoutes(
            HttpUserPermissionRoutes::get()
        )
        */
        ->addRoutes(
            HttpUserRoutes::get()
        )
        ->run();
} catch (\Throwable $e) {
    //print_r("Message: {$e->getMessage()} - Code: {$e->getCode()} - File: {$e->getFile()} - Line: {$e->getLine()}");
    print_r($e);
}
