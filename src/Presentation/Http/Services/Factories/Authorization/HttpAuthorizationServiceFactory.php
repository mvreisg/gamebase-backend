<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authorization;

use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo\PdoRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBSectorRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserSectorPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;

class HttpAuthorizationServiceFactory
{
    public static function make(): AuthorizationService
    {
        $repositoryConnection = PdoRepositoryConnection::make();

        $userRepository = new MariaDBUserRepository(
            $repositoryConnection
        );

        $permissionRepository = new MariaDBPermissionRepository(
            $repositoryConnection
        );

        $sectorRepository = new MariaDBSectorRepository(
            $repositoryConnection
        );

        $userSectorPermissionRepository = new MariaDBUserSectorPermissionRepository(
            $repositoryConnection
        );

        $service = new AuthorizationService(
            $userRepository,
            $permissionRepository,
            $sectorRepository,
            $userSectorPermissionRepository
        );

        return $service;
    }
}
