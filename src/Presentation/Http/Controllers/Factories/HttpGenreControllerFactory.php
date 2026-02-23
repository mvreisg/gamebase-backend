<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Genre\GenreService;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo\PdoRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\EncryptionAdapter;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGenreRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpGenreController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authorization\HttpAuthorizationServiceFactory;

class HttpGenreControllerFactory
{
    public static function make(): HttpGenreController
    {
        try {
            $repositoryConnection = PdoRepositoryConnection::make();

            $genreRepository = new MariaDBGenreRepository(
                $repositoryConnection
            );

            $encrypter = EncryptionAdapter::make();

            $authenticationService = HttpAuthenticationServiceFactory::make(
                $repositoryConnection,
                $encrypter
            );

            $genreService = new GenreService(
                $genreRepository
            );

            $authorizationService = HttpAuthorizationServiceFactory::make();

            $controller = new HttpGenreController(
                $genreService,
                $authenticationService,
                $authorizationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
