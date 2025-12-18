<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Permission\Exceptions\PermissionServiceDuplicatedNameException;
use Mvreisg\GamebaseBackend\Application\Services\Permission\Exceptions\PermissionServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\Permission\Exceptions\PermissionServiceInvalidNameException;
use Mvreisg\GamebaseBackend\Application\Services\Permission\Exceptions\PermissionServiceUnexistantPermissionException;
use Mvreisg\GamebaseBackend\Application\Services\Permission\PermissionService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpForbiddenException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpNotFoundException;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpPermissionController
{
    private PermissionService $permissionService;
    private AuthenticationService $authenticationService;

    public function __construct(
        PermissionService $permissionService,
        AuthenticationService $authenticationService
    ) {
        $this->permissionService = $permissionService;
        $this->authenticationService = $authenticationService;
    }

    public function insert(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $name = $request->getParsedBodyPartOrDieTrying('name');
            $isActive = $request->getParsedBodyPartOrDieTrying('isActive');

            $permission = $this->permissionService->insert($name, $isActive);

            $response
                ->setBody([
                    'data' => [
                        'id' => $permission->getId(),
                        'name' => $permission->getName(),
                        'isActive' => $permission->getIsActive()
                    ]
                ])
                ->setStatusCreated()
                ->sendJson();
        } catch (PermissionServiceDuplicatedNameException $e) {
            throw new HttpForbiddenException(
                "Forbidden: {$e->getMessage()}",
                $e
            );
        } catch (PermissionServiceInvalidNameException $e) {
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
            $name = $request->getParsedBodyPartOrDieTrying('name');
            $isActive = $request->getParsedBodyPartOrDieTrying('isActive');

            $wasUpdated = $this->permissionService->update($id, $name, $isActive);

            $response
                ->setBody([
                    'hasChanged' => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (PermissionServiceDuplicatedNameException $e) {
            throw new HttpForbiddenException(
                "Forbidden: {$e->getMessage()}",
                $e
            );
        } catch (
            PermissionServiceInvalidIdException |
            PermissionServiceInvalidNameException
            $e
        ) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (PermissionServiceUnexistantPermissionException $e) {
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

            $wasUpdated = $this->permissionService->setIsActive($id, $isActive);

            $response
                ->setBody([
                    'hasChanged' => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (PermissionServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (PermissionServiceUnexistantPermissionException $e) {
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

            $permission = $this->permissionService->findById($id);

            $response
                ->setBody([
                    'data' => [
                        'id' => $permission->getId(),
                        'name' => $permission->getName(),
                        'isActive' => $permission->getIsActive()
                    ]
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (PermissionServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (PermissionServiceUnexistantPermissionException $e) {
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
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $permissions = $this->permissionService->findAll();

            $numberOfPermissionsFound = count($permissions);
            if ($numberOfPermissionsFound === 0) {
                throw new HttpNotFoundException(
                    "Nothing found!"
                );
            }

            $data = [];
            foreach ($permissions as $permission) {
                $data[] = [
                    'id' => $permission->getId(),
                    'name' => $permission->getName(),
                    'isActive' => $permission->getIsActive()
                ];
            }

            $response
                ->setBody([
                    'number' => $numberOfPermissionsFound,
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
