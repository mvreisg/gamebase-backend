<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\GameGenre\GameGenreService;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo\PdoRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\EncryptionAdapter;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGameGenreRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGameRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBGenreRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpGameGenreController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authorization\HttpAuthorizationServiceFactory;

class HttpGameGenreControllerFactory
{
    public static function make(): HttpGameGenreController
    {
        try {
            $repositoryConnection = PdoRepositoryConnection::make();

            $gameGenreRepository = new MariaDBGameGenreRepository(
                $repositoryConnection
            );

            $gameRepository = new MariaDBGameRepository(
                $repositoryConnection
            );

            $genreRepository = new MariaDBGenreRepository(
                $repositoryConnection
            );

            $gameGenreService = new GameGenreService(
                $gameGenreRepository,
                $gameRepository,
                $genreRepository
            );

            $encrypter = EncryptionAdapter::make();

            $authenticationService = HttpAuthenticationServiceFactory::make(
                $repositoryConnection,
                $encrypter
            );

            $authorizationService = HttpAuthorizationServiceFactory::make();

            $controller = new HttpGameGenreController(
                $gameGenreService,
                $authenticationService,
                $authorizationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
