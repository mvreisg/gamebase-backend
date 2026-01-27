<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Login\AuthenticationLoginInfo;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Login\AuthenticationLoginStates;
use Mvreisg\GamebaseBackend\Domain\Data\Password;
use Mvreisg\GamebaseBackend\Domain\Data\Username;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpAuthenticationController
{
    private AuthenticationService $authenticationService;

    public function __construct(
        AuthenticationService $authenticationService
    ) {
        $this->authenticationService = $authenticationService;
    }

    public function handleLogin(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $username = $request->getBodyOrDieTrying("username");
            $password = $request->getBodyOrDieTrying("password");
            $oneWeekLogin = $request->getBodyOrDieTrying("one_week_login");

            $result = $this->authenticationService->tryLogin(
                new AuthenticationLoginInfo(
                    Username::make($username),
                    Password::make($password),
                    $oneWeekLogin
                )
            );
            $state = $result->getState();
            switch ($state) {
                case AuthenticationLoginStates::New:
                    $token = $result->getToken();
                    $oneDayInSeconds = 60 * 60 * 24;
                    $timeToExpireInSeconds = $oneWeekLogin ? $oneDayInSeconds * 7 : $oneDayInSeconds;
                    $response
                        ->setBody([
                            "seconds_to_expire" => $timeToExpireInSeconds,
                            "token" => $token->getToken(),
                            "login_data" => $result->getData()->toArray()
                        ])
                        ->setStatusCreated()
                        ->setContentTypeAsJson();
                    return $response;
                case AuthenticationLoginStates::Existing:
                    $token = $result->getToken();
                    $response
                        ->setBody([
                            "token" => $token->getToken(),
                            "login_data" => $result->getData()->toArray()
                        ])
                        ->setStatusOk()
                        ->setContentTypeAsJson();
                    return $response;
                default:
                    $response
                        ->setStatusInternalServerError()
                        ->setContentTypeAsJson();
                    return $response;
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function handleValidation(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $result = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );
            $response
                ->setStatusOk()
                ->setBody([
                    "login_info" => $result->toArray()
                ])
                ->setContentTypeAsJson();
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function handleLogoff(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $result = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );
            $this->authenticationService->tryLogoff($result->getToken());
            $response
                ->setStatusOk();
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
