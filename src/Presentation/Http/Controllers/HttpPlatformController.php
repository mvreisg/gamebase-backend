<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Platform\PlatformService;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Data\Platform;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpPlatformController
{
    private PlatformService $platformService;
    private AuthenticationService $authenticationService;

    public function __construct(
        PlatformService $platformService,
        AuthenticationService $authenticationService
    ) {
        $this->platformService = $platformService;
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

            $name = $request->getBodyOrDieTrying("name");
            $isActive = $request->getBodyOrDieTrying("is_active");

            $platform = $this->platformService->insert(
                new Platform(
                    null,
                    Name::make($name),
                    $isActive
                )
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $platform->getIdValue(),
                        "name" => $platform->getNameValue(),
                        "isActive" => $platform->getIsActive()
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
            $name = $request->getBodyOrDieTrying("name");
            $isActive = $request->getBodyOrDieTrying("is_active");

            $wasUpdated = $this->platformService->update(
                new Platform(
                    Id::make($id),
                    Name::make($name),
                    $isActive
                )
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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");
            $isActive = $request->getBodyOrDieTrying("is_active");

            $wasUpdated = $this->platformService->setIsActive(
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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");

            $platform = $this->platformService->findById(
                Id::make($id)
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $platform->getIdValue(),
                        "name" => $platform->getNameValue(),
                        "isActive" => $platform->getIsActive()
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

            $platforms = $this->platformService->findAll();

            if ($platforms->count() === 0) {
                $response
                    ->setBody([
                        "message" => "Nothing found!"
                    ])
                    ->setStatusNoContent()
                    ->setContentTypeAsJson();
                return $response;
            }

            $data = [];
            foreach ($platforms->fetchAll() as $platform) {
                $data[] = [
                    "id" => $platform->getIdValue(),
                    "name" => $platform->getNameValue(),
                    "isActive" => $platform->getIsActive()
                ];
            }

            $response
                ->setBody([
                    "number_found" => $platforms->count(),
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
