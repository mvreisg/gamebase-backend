<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\SectorPermissionService;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermission;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
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

    public function insert(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $sectorId = $request->getBodyOrDieTrying("sector_id");
            $permissionId = $request->getBodyOrDieTrying("permission_id");

            $sectorPermission = $this->sectorPermissionService->insert(
                new SectorPermission(
                    Id::make($sectorId),
                    Id::make($permissionId)
                )
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $sectorPermission->getIdValue(),
                        "sector_id" => $sectorPermission->getSectorIdValue(),
                        "permission_id" => $sectorPermission->getPermissionIdValue()
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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");
            $sectorId = $request->getBodyOrDieTrying("sector_id");
            $permissionId = $request->getBodyOrDieTrying("permission_id");

            $sectorPermission = new SectorPermission(
                Id::make($sectorId),
                Id::make($permissionId)
            );
            $sectorPermission->setId(Id::make($id));

            $wasUpdated = $this->sectorPermissionService->update(
                $sectorPermission
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

    public function delete(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");

            $wasDeleted = $this->sectorPermissionService->delete(
                Id::make($id)
            );

            $response
                ->setBody([
                    "was_deleted" => $wasDeleted
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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");

            $sectorPermission = $this->sectorPermissionService->findById(
                Id::make($id)
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $sectorPermission->getIdValue(),
                        "sector_id" => $sectorPermission->getSectorIdValue(),
                        "permission_id" => $sectorPermission->getPermissionIdValue()
                    ]
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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $sectorPermissions = $this->sectorPermissionService->findAll();

            if ($sectorPermissions->isEmpty()) {
                $response
                    ->setBody([
                        "message" => "Nothing found!"
                    ])
                    ->setStatusOk()
                    ->setContentTypeAsJson();
                return $response;
            }

            $data = [];
            foreach ($sectorPermissions->fetchAll() as $sectorPermission) {
                $data[] = [
                    "id" => $sectorPermission->getIdValue(),
                    "sector_id" => $sectorPermission->getSectorIdValue(),
                    "permission_id" => $sectorPermission->getPermissionIdValue()
                ];
            }

            $response
                ->setBody([
                    "number_found" => $sectorPermissions->count(),
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
