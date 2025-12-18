<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\UserPermission\Exceptions\UserPermissionServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\UserPermission\Exceptions\UserPermissionServiceInvalidPermissionIdException;
use Mvreisg\GamebaseBackend\Application\Services\UserPermission\Exceptions\UserPermissionServiceInvalidUserIdException;
use Mvreisg\GamebaseBackend\Application\Services\UserPermission\Exceptions\UserPermissionServiceUnexistantPermissionException;
use Mvreisg\GamebaseBackend\Application\Services\UserPermission\Exceptions\UserPermissionServiceUnexistantUserException;
use Mvreisg\GamebaseBackend\Application\Services\UserPermission\Exceptions\UserPermissionServiceUnexistantUserPermissionException;
use Mvreisg\GamebaseBackend\Application\Services\UserPermission\UserPermissionService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpNotFoundException;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpUserPermissionController
{
    private UserPermissionService $userPermissionService;
    private AuthenticationService $authenticationService;

    public function __construct(
        UserPermissionService $userPermissionService,
        AuthenticationService $authenticationService
    ) {
        $this->userPermissionService = $userPermissionService;
        $this->authenticationService = $authenticationService;
    }

    public function insert(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $userId = $request->getParsedBodyPartOrDieTrying("userId");
            $permissionId = $request->getParsedBodyPartOrDieTrying("permissionId");

            $userPermission = $this->userPermissionService->insert($userId, $permissionId);

            $response
                ->setBody([
                    "data" => [
                        "id" => $userPermission->getId(),
                        "userId" => $userPermission->getUserId(),
                        "permissionId" => $userPermission->getPermissionId()
                    ]
                ])
                ->setStatusCreated()
                ->sendJson();
        } catch (
            UserPermissionServiceUnexistantUserException |
            UserPermissionServiceUnexistantPermissionException
            $e
        ) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
                $e
            );
        } catch (
            UserPermissionServiceInvalidUserIdException |
            UserPermissionServiceInvalidPermissionIdException
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
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");
            $userId = $request->getParsedBodyPartOrDieTrying("userId");
            $permissionId = $request->getParsedBodyPartOrDieTrying("permissionId");

            $wasUpdated = $this->userPermissionService->update($id, $userId, $permissionId);
            $response
                ->setBody([
                    "hasChanged" => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (
            UserPermissionServiceInvalidIdException |
            UserPermissionServiceInvalidUserIdException |
            UserPermissionServiceInvalidPermissionIdException
            $e
        ) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (
            UserPermissionServiceUnexistantUserException |
            UserPermissionServiceUnexistantPermissionException |
            UserPermissionServiceUnexistantUserPermissionException
            $e
        ) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");

            $wasDeleted = $this->userPermissionService->delete($id);
            $response
                ->setBody([
                    "wasDeleted" => $wasDeleted
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (UserPermissionServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (UserPermissionServiceUnexistantUserPermissionException $e) {
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
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");

            $userPermission = $this->userPermissionService->findById($id);

            if ($userPermission === null) {
                throw new HttpNotFoundException(
                    "User permission with the id $id not found!"
                );
            }

            $response
                ->setBody([
                    "data" => [
                        "id" => $userPermission->getId(),
                        "userId" => $userPermission->getUserId(),
                        "permissionId" => $userPermission->getPermissionId()
                    ]
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (HttpNotFoundException $e) {
            throw $e;
        } catch (UserPermissionServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (UserPermissionServiceUnexistantUserPermissionException $e) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
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
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $userPermissions = $this->userPermissionService->findAll();

            $numberOfUserPermissions = count($userPermissions);
            if ($numberOfUserPermissions === 0) {
                throw new HttpNotFoundException(
                    "No user permissions found!"
                );
            }

            $data = [];
            foreach ($userPermissions as $userPermission) {
                $data[] = [
                    "id" => $userPermission->getId(),
                    "userId" => $userPermission->getUserId(),
                    "permissionId" => $userPermission->getPermissionId()
                ];
            }

            $response
                ->setBody([
                    "number" => $numberOfUserPermissions,
                    "data" => $data
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
