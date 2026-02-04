<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Connections\MariaDBRepositoryConnection;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpAuthenticationController;
use Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication\HttpAuthenticationServiceFactory;

class HttpAuthenticationControllerFactory
{
    public static function make(): HttpAuthenticationController
    {
        try {
            $repositoryConnection = MariaDBRepositoryConnection::get();

            $encrypter = new DefuseEncryption();

            $service = HttpAuthenticationServiceFactory::make(
                $repositoryConnection,
                $encrypter
            );

            $controller = new HttpAuthenticationController(
                $service
            );

            return $controller;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
