<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Platform\Exceptions\PlatformServiceDuplicatedNameException;
use Mvreisg\GamebaseBackend\Application\Services\Platform\Exceptions\PlatformServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\Platform\Exceptions\PlatformServiceInvalidNameException;
use Mvreisg\GamebaseBackend\Application\Services\Platform\Exceptions\PlatformServiceUnexistantPlatformException;
use Mvreisg\GamebaseBackend\Application\Services\Platform\PlatformService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpForbiddenException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpNotFoundException;
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

    public function insert(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $name = $request->getParsedBodyPartOrDieTrying("name");
            $isActive = $request->getParsedBodyPartOrDieTrying("isActive");

            $platform = $this->platformService->insert($name, $isActive);

            $response
                ->setBody([
                    "data" => [
                        "id" => $platform->getId(),
                        "name" => $platform->getName(),
                        "isActive" => $platform->getIsActive()
                    ]
                ])
                ->setStatusCreated()
                ->sendJson();
        } catch (PlatformServiceDuplicatedNameException $e) {
            throw new HttpForbiddenException(
                "Forbidden: {$e->getMessage()}",
                $e
            );
        } catch (PlatformServiceInvalidNameException $e) {
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
            $name = $request->getParsedBodyPartOrDieTrying("name");
            $isActive = $request->getParsedBodyPartOrDieTrying("isActive");

            $wasUpdated = $this->platformService->update($id, $name, $isActive);

            $response
                ->setBody([
                    "hasChanged" => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (PlatformServiceDuplicatedNameException $e) {
            throw new HttpForbiddenException(
                "Forbidden: {$e->getMessage()}",
                $e
            );
        } catch (
            PlatformServiceInvalidIdException |
            PlatformServiceInvalidNameException
            $e
        ) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (PlatformServiceUnexistantPlatformException $e) {
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
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");
            $isActive = $request->getParsedBodyPartOrDieTrying("isActive");

            $wasUpdated = $this->platformService->setIsActive($id, $isActive);

            $response
                ->setBody([
                    "hasChanged" => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (PlatformServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (PlatformServiceUnexistantPlatformException $e) {
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

            $platform = $this->platformService->findById($id);

            $response
                ->setBody([
                    "data" => [
                        "id" => $platform->getId(),
                        "name" => $platform->getName(),
                        "isActive" => $platform->getIsActive()
                    ]
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (PlatformServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (PlatformServiceUnexistantPlatformException $e) {
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

            $platforms = $this->platformService->findAll();

            $numberOfPlatformsFound = count($platforms);
            if ($numberOfPlatformsFound === 0) {
                throw new HttpNotFoundException(
                    "Nothing found!"
                );
            }

            $data = [];
            foreach ($platforms as $platform) {
                $data[] = [
                    "id" => $platform->getId(),
                    "name" => $platform->getName(),
                    "isActive" => $platform->getIsActive()
                ];
            }

            $response
                ->setBody([
                    "number" => $numberOfPlatformsFound,
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
