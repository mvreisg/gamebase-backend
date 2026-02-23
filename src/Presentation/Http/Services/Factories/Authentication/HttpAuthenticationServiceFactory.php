<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Services\Factories\Authentication;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Clock\JwtAuthenticationTokenClock;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Decoder\JwtAuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Encoder\JwtAuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Validator\Decoded\JwtDecodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Validator\Encoded\JwtEncodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Redis\Token\RedisTokenCache;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo\PdoRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\Predis\PredisConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\EncryptionAdapter;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserSectorPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\MariaDBUserRepository;

class HttpAuthenticationServiceFactory
{
    public static function make(): AuthenticationService
    {
        $repositoryConnection = PdoRepositoryConnection::make();

        $userRepository = new MariaDBUserRepository(
            $repositoryConnection
        );

        $userSectorPermissionRepository = new MariaDBUserSectorPermissionRepository(
            $repositoryConnection
        );

        $cacheConnection = PredisConnection::make();

        $tokenCache = new RedisTokenCache(
            $cacheConnection
        );

        $encrypter = EncryptionAdapter::make();

        $jwtAuthenticationTokenClock = new JwtAuthenticationTokenClock();

        $authenticationTokenEncoder = new JwtAuthenticationTokenEncoder(
            $jwtAuthenticationTokenClock
        );

        $authenticationTokenDecoder = new JwtAuthenticationTokenDecoder(
            $jwtAuthenticationTokenClock
        );

        $decodedAuthenticationTokenValidator = new JwtDecodedAuthenticationTokenValidator(
            $jwtAuthenticationTokenClock
        );

        $encodedAuthenticationTokenValidator = new JwtEncodedAuthenticationTokenValidator(
            $authenticationTokenDecoder,
            $decodedAuthenticationTokenValidator
        );

        $service = new AuthenticationService(
            $userRepository,
            $tokenCache,
            $encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $userSectorPermissionRepository,
            $encodedAuthenticationTokenValidator
        );

        return $service;
    }
}
