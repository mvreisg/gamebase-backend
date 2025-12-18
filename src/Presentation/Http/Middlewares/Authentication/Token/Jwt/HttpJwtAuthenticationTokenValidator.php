<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceCacheException;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceUnauthorizedException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpForbiddenException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpUnauthorizedException;

class HttpJwtAuthenticationTokenValidator
{
    public static function validate(string $header, AuthenticationService $authenticationService): void
    {
        try {
            $token = HttpJwtAuthenticationTokenRetriever::retrieve($header);
            $authenticationService->validateLogin($token);
        } catch (AuthenticationServiceUnauthorizedException $e) {
            throw new HttpUnauthorizedException(
                "Unauthorized: {$e->getMessage()}",
                $e
            );
        } catch (AuthenticationServiceCacheException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (
            HttpUnauthorizedException |
            HttpForbiddenException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }
}
