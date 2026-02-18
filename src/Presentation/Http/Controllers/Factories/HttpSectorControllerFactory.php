<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Sector\SectorService;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\EncryptionAdapter;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Connections\MariaDBRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpSectorController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;

class HttpSectorControllerFactory
{
    public static function make(): HttpSectorController
    {
        try {
            $repositoryConnection = MariaDBRepositoryConnection::get();

            $sectorRepository = new MariaDBSectorRepository(
                $repositoryConnection
            );

            $sectorService = new SectorService(
                $sectorRepository
            );

            $encrypter = new EncryptionAdapter();

            $authenticationService = HttpAuthenticationServiceFactory::make(
                $repositoryConnection,
                $encrypter
            );

            $controller = new HttpSectorController(
                $sectorService,
                $authenticationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
