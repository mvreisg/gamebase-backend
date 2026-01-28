<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Genre\GenreService;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Connections\MariaDBRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGenreRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpGenreController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;

class HttpGenreControllerFactory
{
    public static function make(): HttpGenreController
    {
        try {
            $repositoryConnection = MariaDBRepositoryConnection::get();

            $genreRepository = new MariaDBGenreRepository(
                $repositoryConnection
            );

            $authenticationService = HttpAuthenticationServiceFactory::make();

            $genreService = new GenreService(
                $genreRepository
            );

            $controller = new HttpGenreController(
                $genreService,
                $authenticationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
