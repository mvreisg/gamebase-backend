<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\GenreService;
use Mvreisg\GamebaseBackend\Presentation\Controllers\GenreController;
use Mvreisg\GamebaseBackend\Infrastructure\Database\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBGenreRepository;

class GenreControllerFactory
{
    public static function get(): GenreController
    {
        $repository = new MariaDBGenreRepository(MariaDBConnection::get());
        $service = new GenreService($repository);
        $controller = new GenreController($service);
        return $controller;
    }
}
