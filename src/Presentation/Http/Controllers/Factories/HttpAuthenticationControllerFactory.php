<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\JwtTokenAuthentication;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Connections\RedisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\RedisUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Connections\MariaDBConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\HttpAuthenticationController;

class HttpAuthenticationControllerFactory
{
    public static function make(): HttpAuthenticationController
    {
        try {
            $repositoryConnection = MariaDBConnection::get();

            $repository = new MariaDBUserRepository(
                $repositoryConnection
            );

            $encrypter = new DefuseEncryption();

            $cacheConnection = RedisConnection::get();

            $cache = new RedisUserCache(
                $cacheConnection
            );

            $authenticator = new JwtTokenAuthentication();

            $service = new AuthenticationService(
                $repository,
                $encrypter,
                $cache,
                $authenticator
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
