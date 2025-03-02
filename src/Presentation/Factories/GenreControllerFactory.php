<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\GenreService;
use Mvreisg\GamebaseBackend\Presentation\Controllers\GenreController;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBGenreRepository;

/**
 * Genre controller factory class.
 */
class GenreControllerFactory
{
    /**
     * Static method that returns the Genre controller using the Factory pattern.
     * @return GenreController The Genre controller instance.
     */
    public static function get(): GenreController
    {
        $repository = new MariaDBGenreRepository(MariaDBConnection::get());
        $service = new GenreService($repository);
        $controller = new GenreController($service);
        return $controller;
    }
}
