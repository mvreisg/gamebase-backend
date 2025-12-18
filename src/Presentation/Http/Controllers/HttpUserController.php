<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\User\Exceptions\UserServiceDuplicatedUsernameException;
use Mvreisg\GamebaseBackend\Application\Services\User\Exceptions\UserServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\User\Exceptions\UserServiceInvalidPasswordException;
use Mvreisg\GamebaseBackend\Application\Services\User\Exceptions\UserServiceInvalidUsernameException;
use Mvreisg\GamebaseBackend\Application\Services\User\Exceptions\UserServiceUnexistantUserException;
use Mvreisg\GamebaseBackend\Application\Services\User\UserService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteQueryTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpForbiddenException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpNotFoundException;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpUserController
{
    private UserService $userService;
    private AuthenticationService $authenticationService;

    public function __construct(
        UserService $userService,
        AuthenticationService $authenticationService
    ) {
        $this->userService = $userService;
        $this->authenticationService = $authenticationService;
    }

    public function insert(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $username = $request->getParsedBodyPartOrDieTrying('username');
            $password = $request->getParsedBodyPartOrDieTrying('password');
            $isActive = $request->getParsedBodyPartOrDieTrying('isActive');

            $user = $this->userService->insert($username, $password, $isActive);

            $response
                ->setBody([
                    'data' => [
                        'id' => $user->getId(),
                        'username' => $user->getUsername(),
                        'password' => $user->getPassword(),
                        'isActive' => $user->getIsActive()
                    ]
                ])
                ->setStatusCreated()
                ->sendJson();
        } catch (UserServiceDuplicatedUsernameException $e) {
            throw new HttpForbiddenException(
                "Forbidden: {$e->getMessage()}",
                $e
            );
        } catch (
            UserServiceInvalidUsernameException |
            UserServiceInvalidPasswordException
            $e
        ) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying('id');
            $username = $request->getParsedBodyPartOrDieTrying('username');
            $password = $request->getParsedBodyPartOrDieTrying('password');
            $isActive = $request->getParsedBodyPartOrDieTrying('isActive');

            $wasUpdated = $this->userService->update($id, $username, $password, $isActive);

            $response
                ->setBody([
                    'hasChanged' => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (UserServiceDuplicatedUsernameException $e) {
            throw new HttpForbiddenException(
                "Forbidden: {$e->getMessage()}",
                $e
            );
        } catch (
            UserServiceInvalidIdException |
            UserServiceInvalidUsernameException |
            UserServiceInvalidPasswordException
            $e
        ) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (UserServiceUnexistantUserException $e) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying('id');
            $isActive = $request->getParsedBodyPartOrDieTrying('isActive');

            $wasUpdated = $this->userService->setIsActive($id, $isActive);

            $response
                ->setBody([
                    'hasChanged' => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (UserServiceUnexistantUserException $e) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying('id');

            $user = $this->userService->findById($id);

            $data = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'isActive' => $user->getIsActive()
            ];

            $showPasswordQuery = $request->getQueryOrDieTrying('showPassword');
            if ($showPasswordQuery) {
                if (
                    $showPasswordQuery->getType() === HttpRouteQueryTypesEnum::Boolean &&
                    $showPasswordQuery->getValue() === true
                ) {
                    $data['password'] = $user->getPassword();
                }
            }

            $response
                ->setBody([
                    'data' => $data
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (UserServiceUnexistantUserException $e) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findByUsername(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $username = $request->getParamOrDieTrying('username');

            $user = $this->userService->findByUsername($username);

            $data = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'isActive' => $user->getIsActive()
            ];

            $showPasswordQuery = $request->getQueryOrDieTrying('showPassword');
            if ($showPasswordQuery) {
                if (
                    $showPasswordQuery->getType() === HttpRouteQueryTypesEnum::Boolean &&
                    $showPasswordQuery->getValue() === true
                ) {
                    $data['password'] = $user->getPassword();
                }
            }

            $response
                ->setBody([
                    'data' => $data
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (UserServiceUnexistantUserException $e) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
                $e
            );
        } catch (UserServiceInvalidUsernameException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $users = $this->userService->findAll();

            $numberOfUsersFound = count($users);
            if ($numberOfUsersFound === 0) {
                throw new HttpNotFoundException(
                    "Nothing found!"
                );
            }

            $showPasswordQuery = $request->getQueryOrDieTrying('showPassword');
            $showPassword = false;
            if ($showPasswordQuery) {
                if (
                    $showPasswordQuery->getType() === HttpRouteQueryTypesEnum::Boolean &&
                    $showPasswordQuery->getValue() === true
                ) {
                    $showPassword = true;
                }
            }

            $data = [];
            foreach ($users as $user) {
                $value = [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'isActive' => $user->getIsActive()
                ];

                if ($showPassword) {
                    $value['password'] = $user->getPassword();
                }

                $data[] = $value;
            }

            $response
                ->setBody([
                    'number' => $numberOfUsersFound,
                    'data' => $data
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (HttpNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
