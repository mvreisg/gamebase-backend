<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\UserPermission\UserPermissionService;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Connections\MariaDBRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpUserPermissionController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;

class HttpUserPermissionControllerFactory
{
    public static function make(): HttpUserPermissionController
    {
        try {
            $repositoryConnection = MariaDBRepositoryConnection::get();

            $userRepository = new MariaDBUserRepository(
                $repositoryConnection
            );

            $permissionRepository = new MariaDBPermissionRepository(
                $repositoryConnection
            );

            $userPermissionRepository = new MariaDBUserPermissionRepository(
                $repositoryConnection
            );

            $userPermissionService = new UserPermissionService(
                $userRepository,
                $permissionRepository,
                $userPermissionRepository
            );

            $authenticationService = HttpAuthenticationServiceFactory::make();

            $controller = new HttpUserPermissionController(
                $userPermissionService,
                $authenticationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
