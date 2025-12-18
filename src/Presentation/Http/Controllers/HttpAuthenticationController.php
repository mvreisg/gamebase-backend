<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\Enums\AuthenticationLoginExistanceStatesEnum;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceUnauthorizedException;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceUnexistantUserException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpUnauthorizedException;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpInternalServerError;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenRetriever;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpAuthenticationController
{
    private AuthenticationService $authenticationService;

    public function __construct(
        AuthenticationService $authenticationService
    ) {
        $this->authenticationService = $authenticationService;
    }

    public function handleLogin(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $request->parseBodyFromJsonString();

            $username = $request->getParsedBodyPartOrDieTrying("username");
            $password = $request->getParsedBodyPartOrDieTrying("password");
            $oneWeek = $request->getParsedBodyPartOrDieTrying("oneWeek");

            $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);
            $state = $result->getState();
            switch ($state) {
                case AuthenticationLoginExistanceStatesEnum::New:
                    $token = $result->getToken();
                    $oneDayInSeconds = 60 * 60 * 24;
                    $timeToExpireInSeconds = $oneWeek ? $oneDayInSeconds * 7 : $oneDayInSeconds;
                    $response
                        ->setBody([
                            "secondsToExpire" => $timeToExpireInSeconds,
                            "token" => $token,
                            "loginInfo" => $result->getDto()
                        ])
                        ->setStatusCreated()
                        ->sendJson();
                    return;
                case AuthenticationLoginExistanceStatesEnum::Existing:
                    $token = $result->getToken();
                    $response
                        ->setBody([
                            "token" => $token,
                            "loginInfo" => $result->getDto()
                        ])
                        ->setStatusOk()
                        ->sendJson();
                    return;
                default:
                    throw new HttpInternalServerError(
                        "Unhandled state: $state"
                    );
            }
        } catch (AuthenticationServiceUnexistantUserException $e) {
            throw new HttpBadRequestException(
                "Bad Request: {$e->getMessage()}",
                $e
            );
        } catch (AuthenticationServiceUnauthorizedException $e) {
            throw new HttpUnauthorizedException(
                "Unauthorized: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function handleValidation(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $result = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );
            $response
                ->setStatusOk()
                ->setBody([
                    "loginInfo" => $result->getDto()
                ])
                ->sendJson();
        } catch (AuthenticationServiceUnauthorizedException $e) {
            throw new HttpUnauthorizedException(
                "Unauthorized: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function handleLogoff(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );
            $token = HttpJwtAuthenticationTokenRetriever::retrieve(
                $request->getHeaderOrDieTrying("Authorization")
            );
            $this->authenticationService->tryLogoff($token);
            $response->setStatusOk();
        } catch (AuthenticationServiceUnauthorizedException $e) {
            throw new HttpUnauthorizedException(
                "Unauthorized: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
