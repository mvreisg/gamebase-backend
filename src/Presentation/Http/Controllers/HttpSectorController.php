<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\Authentication\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Sector\Exceptions\SectorServiceDuplicatedNameException;
use Mvreisg\GamebaseBackend\Application\Services\Sector\Exceptions\SectorServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\Sector\Exceptions\SectorServiceInvalidNameException;
use Mvreisg\GamebaseBackend\Application\Services\Sector\Exceptions\SectorServiceUnexistantSectorException;
use Mvreisg\GamebaseBackend\Application\Services\Sector\SectorService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpInvalidParameterException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpUnauthorizedException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpUndefinedValueException;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpContentTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpStatusCodeTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpForbiddenException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpNotFoundException;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\HttpJwtAuthenticationTokenRetriever;

class HttpSectorController
{
    private SectorService $sectorService;
    private AuthenticationService $authenticationService;

    public function __construct(
        SectorService $sectorService,
        AuthenticationService $authenticationService
    ) {
        $this->sectorService = $sectorService;
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

            $sector = $this->sectorService->insert($name, $isActive);

            $response
                ->setBody([
                    'data' => [
                        'id' => $sector->getId(),
                        'name' => $sector->getName(),
                        'isActive' => $sector->getIsActive()
                    ]
                ])
                ->setStatusCreated()
                ->sendJson();
        } catch (SectorServiceDuplicatedNameException $e) {
            throw new HttpForbiddenException(
                "Forbidden: {$e->getMessage()}",
                $e
            );
        } catch (SectorServiceInvalidNameException $e) {
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

            $wasUpdated = $this->sectorService->update($id, $name, $isActive);

            $response
                ->setBody([
                    'hasChanged' => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (SectorServiceDuplicatedNameException $e) {
            throw new HttpForbiddenException(
                "Forbidden: {$e->getMessage()}",
                $e
            );
        } catch (
            SectorServiceInvalidIdException |
            SectorServiceInvalidNameException
            $e
        ) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (SectorServiceUnexistantSectorException $e) {
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

            $wasUpdated = $this->sectorService->setIsActive($id, $isActive);

            $response
                ->setBody([
                    'hasChanged' => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (SectorServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (SectorServiceUnexistantSectorException $e) {
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

            $sector = $this->sectorService->findById($id);

            $response
                ->setBody([
                    'data' => [
                        'id' => $sector->getId(),
                        'name' => $sector->getName(),
                        'isActive' => $sector->getIsActive()
                    ]
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (SectorServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (SectorServiceUnexistantSectorException $e) {
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

            $sectors = $this->sectorService->findAll();

            $numberOfSectorsFound = count($sectors);
            if ($numberOfSectorsFound === 0) {
                throw new HttpNotFoundException(
                    "Nothing found!"
                );
            }

            $data = [];
            foreach ($sectors as $sector) {
                $data[] = [
                    'id' => $sector->getId(),
                    'name' => $sector->getName(),
                    'isActive' => $sector->getIsActive()
                ];
            }

            $response
                ->setBody([
                    'number' => $numberOfSectorsFound,
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
