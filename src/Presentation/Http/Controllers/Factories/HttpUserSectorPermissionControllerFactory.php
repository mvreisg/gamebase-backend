<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\UserSectorPermission\UserSectorPermissionService;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo\PdoRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\EncryptionAdapter;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserSectorPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpUserSectorPermissionController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authorization\HttpAuthorizationServiceFactory;

class HttpUserSectorPermissionControllerFactory
{
    public static function make(): HttpUserSectorPermissionController
    {
        try {
            $repositoryConnection = PdoRepositoryConnection::make();

            $userRepository = new MariaDBUserRepository(
                $repositoryConnection
            );

            $sectorRepository = new MariaDBSectorRepository(
                $repositoryConnection
            );

            $permissionRepository = new MariaDBPermissionRepository(
                $repositoryConnection
            );

            $userSectorPermissionRepository = new MariaDBUserSectorPermissionRepository(
                $repositoryConnection
            );

            $userSectorPermissionService = new UserSectorPermissionService(
                $userRepository,
                $sectorRepository,
                $permissionRepository,
                $userSectorPermissionRepository
            );

            $encrypter = EncryptionAdapter::make();

            $authenticationService = HttpAuthenticationServiceFactory::make(
                $repositoryConnection,
                $encrypter
            );

            $authorizationService = HttpAuthorizationServiceFactory::make();

            $controller = new HttpUserSectorPermissionController(
                $userSectorPermissionService,
                $authenticationService,
                $authorizationService
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
