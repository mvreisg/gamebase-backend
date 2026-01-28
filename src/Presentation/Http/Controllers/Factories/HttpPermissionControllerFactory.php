<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Permission\PermissionService;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Connections\MariaDBRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPermissionRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpPermissionController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;

class HttpPermissionControllerFactory
{
    public static function make(): HttpPermissionController
    {
        try {
            $repositoryConnection = MariaDBRepositoryConnection::get();

            $permissionRepository = new MariaDBPermissionRepository(
                $repositoryConnection
            );

            $permissionService = new PermissionService(
                $permissionRepository
            );

            $authenticationService = HttpAuthenticationServiceFactory::make();

            $controller = new HttpPermissionController(
                $permissionService,
                $authenticationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
