<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\SectorPermissionService;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Connections\MariaDBRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpSectorPermissionController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;

class HttpSectorPermissionControllerFactory
{
    public static function make(): HttpSectorPermissionController
    {
        try {
            $repositoryConnection = MariaDBRepositoryConnection::get();

            $sectorRepository = new MariaDBSectorRepository(
                $repositoryConnection
            );

            $permissionRepository = new MariaDBPermissionRepository(
                $repositoryConnection
            );

            $sectorPermissionRepository = new MariaDBSectorPermissionRepository(
                $repositoryConnection
            );

            $sectorPermissionService = new SectorPermissionService(
                $sectorRepository,
                $permissionRepository,
                $sectorPermissionRepository
            );

            $authenticationService = HttpAuthenticationServiceFactory::make();

            $controller = new HttpSectorPermissionController(
                $sectorPermissionService,
                $authenticationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
