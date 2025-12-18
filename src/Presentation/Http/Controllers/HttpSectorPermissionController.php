<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\Exceptions\SectorPermissionServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\Exceptions\SectorPermissionServiceInvalidPermissionIdException;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\Exceptions\SectorPermissionServiceInvalidSectorIdException;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\Exceptions\SectorPermissionServiceUnexistantPermissionException;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\Exceptions\SectorPermissionServiceUnexistantSectorException;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\Exceptions\SectorPermissionServiceUnexistantSectorPermissionException;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\SectorPermissionService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpNotFoundException;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpSectorPermissionController
{
    private SectorPermissionService $sectorPermissionService;
    private AuthenticationService $authenticationService;

    public function __construct(
        SectorPermissionService $sectorPermissionService,
        AuthenticationService $authenticationService
    ) {
        $this->sectorPermissionService = $sectorPermissionService;
        $this->authenticationService = $authenticationService;
    }

    public function insert(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $sectorId = $request->getParsedBodyPartOrDieTrying("sectorId");
            $permissionId = $request->getParsedBodyPartOrDieTrying("permissionId");

            $sectorPermission = $this->sectorPermissionService->insert($sectorId, $permissionId);

            $response
                ->setBody([
                    "data" => [
                        "id" => $sectorPermission->getId(),
                        "sectorId" => $sectorPermission->getSectorId(),
                        "permissionId" => $sectorPermission->getPermissionId()
                    ]
                ])
                ->setStatusCreated()
                ->sendJson();
        } catch (
            SectorPermissionServiceUnexistantSectorException |
            SectorPermissionServiceUnexistantPermissionException
            $e
        ) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
                $e
            );
        } catch (
            SectorPermissionServiceInvalidSectorIdException |
            SectorPermissionServiceInvalidPermissionIdException
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
            $sectorId = $request->getParsedBodyPartOrDieTrying("sectorId");
            $permissionId = $request->getParsedBodyPartOrDieTrying("permissionId");

            $wasUpdated = $this->sectorPermissionService->update($id, $sectorId, $permissionId);
            $response
                ->setBody([
                    "hasChanged" => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (
            SectorPermissionServiceInvalidIdException |
            SectorPermissionServiceInvalidSectorIdException |
            SectorPermissionServiceInvalidPermissionIdException
            $e
        ) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (
            SectorPermissionServiceUnexistantSectorException |
            SectorPermissionServiceUnexistantPermissionException |
            SectorPermissionServiceUnexistantSectorPermissionException
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

            $wasDeleted = $this->sectorPermissionService->delete($id);
            $response
                ->setBody([
                    "wasDeleted" => $wasDeleted
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (SectorPermissionServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (SectorPermissionServiceUnexistantSectorPermissionException $e) {
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

            $sectorPermission = $this->sectorPermissionService->findById($id);

            if ($sectorPermission === null) {
                throw new HttpNotFoundException(
                    "Sector permission with the id $id not found!"
                );
            }

            $response
                ->setBody([
                    "data" => [
                        "id" => $sectorPermission->getId(),
                        "sectorId" => $sectorPermission->getSectorId(),
                        "permissionId" => $sectorPermission->getPermissionId()
                    ]
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (HttpNotFoundException $e) {
            throw $e;
        } catch (SectorPermissionServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (SectorPermissionServiceUnexistantSectorPermissionException $e) {
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

            $sectorPermissions = $this->sectorPermissionService->findAll();

            $numberOfSectorPermissions = count($sectorPermissions);
            if ($numberOfSectorPermissions === 0) {
                throw new HttpNotFoundException(
                    "No sector permissions found!"
                );
            }

            $data = [];
            foreach ($sectorPermissions as $sectorPermission) {
                $data[] = [
                    "id" => $sectorPermission->getId(),
                    "sectorId" => $sectorPermission->getSectorId(),
                    "permissionId" => $sectorPermission->getPermissionId()
                ];
            }

            $response
                ->setBody([
                    "number" => $numberOfSectorPermissions,
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
