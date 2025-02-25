<?php

namespace Mvreisg\GamebaseBackend\Presentation\Factories;

use Mvreisg\GamebaseBackend\Application\Services\UserService;
use Mvreisg\GamebaseBackend\Infrastructure\Database\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Controllers\UserController;

class UserControllerFactory
{
    public static function get(): UserController
    {
        $repository = new MariaDBUserRepository(MariaDBConnection::get());
        $service = new UserService($repository);
        $controller = new UserController($service);
        return $controller;
    }
}
