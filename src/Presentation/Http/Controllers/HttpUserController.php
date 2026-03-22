<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Application\Services\User\UserService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Enums\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Enums\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRequestBodyPartTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteQueryTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpUserController
{
    private UserService $userService;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        UserService $userService,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService
    ) {
        $this->userService = $userService;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    public function insert(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $validationResult = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $this->authorizationService->check(
                $validationResult->getUserSectorPermissionCollection(),
                SectorTypes::User,
                PermissionTypes::Create
            );

            $username = $request->getBodyOrDieTrying("username", HttpRequestBodyPartTypes::String);
            $password = $request->getBodyOrDieTrying("password", HttpRequestBodyPartTypes::String);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);

            $user = $this->userService->insert(
                new User(
                    Username::make($username),
                    DecodedPassword::make($password),
                    $isActive
                )
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $user->getIdValue(),
                        "username" => $user->getUsernameValue(),
                        "password" => $user->getPasswordValue(),
                        "isActive" => $user->getIsActive()
                    ]
                ])
                ->setStatusCreated()
                ->setContentTypeAsJson();
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $validationResult = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $this->authorizationService->check(
                $validationResult->getUserSectorPermissionCollection(),
                SectorTypes::User,
                PermissionTypes::Update
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);
            $username = $request->getBodyOrDieTrying("username", HttpRequestBodyPartTypes::String);
            $password = $request->getBodyOrDieTrying("password", HttpRequestBodyPartTypes::String);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);

            $user = new User(
                Username::make($username),
                DecodedPassword::make($password),
                $isActive
            );
            $user->setId(Id::make($id));

            $wasUpdated = $this->userService->update(
                $user
            );

            $response
                ->setBody([
                    "was_updated" => $wasUpdated
                ])
                ->setStatusOk()
                ->setContentTypeAsJson();
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $validationResult = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $this->authorizationService->check(
                $validationResult->getUserSectorPermissionCollection(),
                SectorTypes::User,
                PermissionTypes::Activate
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);

            $wasUpdated = $this->userService->setIsActive(
                Id::make($id),
                $isActive
            );

            $response
                ->setBody([
                    "was_updated" => $wasUpdated
                ])
                ->setStatusOk()
                ->setContentTypeAsJson();
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $validationResult = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $this->authorizationService->check(
                $validationResult->getUserSectorPermissionCollection(),
                SectorTypes::User,
                PermissionTypes::List
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);

            $user = $this->userService->findById(
                Id::make($id)
            );

            $data = [
                "id" => $user->getIdValue(),
                "username" => $user->getUsernameValue(),
                "is_active" => $user->getIsActive()
            ];

            $showPasswordQuery = $request->getQueryOrDieTrying("show_password");
            if (isset($showPasswordQuery)) {
                if (
                    $showPasswordQuery->getType() === HttpRouteQueryTypes::Boolean &&
                    $showPasswordQuery->getValue() === true
                ) {
                    $data["password"] = $user->getPasswordValue();
                }
            }

            $response
                ->setBody([
                    "data" => $data
                ])
                ->setStatusOk()
                ->setContentTypeAsJson();
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findByUsername(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $validationResult = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $this->authorizationService->check(
                $validationResult->getUserSectorPermissionCollection(),
                SectorTypes::User,
                PermissionTypes::List
            );

            $username = $request->getParamOrDieTrying("username", HttpRouteParameterTypes::Text);

            $user = $this->userService->findByUsername(
                Username::make($username)
            );

            $data = [
                "id" => $user->getIdValue(),
                "username" => $user->getUsernameValue(),
                "is_active" => $user->getIsActive()
            ];

            $showPasswordQuery = $request->getQueryOrDieTrying("show_password");
            if (isset($showPasswordQuery)) {
                if (
                    $showPasswordQuery->getType() === HttpRouteQueryTypes::Boolean &&
                    $showPasswordQuery->getValue() === true
                ) {
                    $data["password"] = $user->getPasswordValue();
                }
            }

            $response
                ->setBody([
                    "data" => $data
                ])
                ->setStatusOk()
                ->setContentTypeAsJson();
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $validationResult = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $this->authorizationService->check(
                $validationResult->getUserSectorPermissionCollection(),
                SectorTypes::User,
                PermissionTypes::List
            );

            $users = $this->userService->findAll();

            if ($users->count() === 0) {
                $response
                    ->setBody([
                        "message" => "Nothing found!"
                    ])
                    ->setStatusNoContent()
                    ->setContentTypeAsJson();
                return $response;
            }

            $showPasswordQuery = $request->getQueryOrDieTrying("show_password");
            $showPassword = false;
            if (isset($showPasswordQuery)) {
                if (
                    $showPasswordQuery->getType() === HttpRouteQueryTypes::Boolean &&
                    $showPasswordQuery->getValue() === true
                ) {
                    $showPassword = true;
                }
            }

            $data = [];
            foreach ($users->fetchAll() as $user) {
                $value = [
                    "id" => $user->getIdValue(),
                    "username" => $user->getUsernameValue(),
                    "isActive" => $user->getIsActive()
                ];

                if ($showPassword) {
                    $value["password"] = $user->getPasswordValue();
                }

                $data[] = $value;
            }

            $response
                ->setBody([
                    "number_found" => $users->count(),
                    "data" => $data
                ])
                ->setStatusOk()
                ->setContentTypeAsJson();
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
