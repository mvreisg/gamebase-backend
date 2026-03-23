<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Application\Services\Sector\SectorService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Entities\Sector;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Utils\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Utils\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpSectorController
{
    private SectorService $sectorService;
    private AuthorizationService $authorizationService;
    private AuthenticationTokenDecoder $authenticationTokenDecoder;

    public function __construct(
        SectorService $sectorService,
        AuthorizationService $authorizationService,
        AuthenticationTokenDecoder $authenticationTokenDecoder
    ) {
        $this->sectorService = $sectorService;
        $this->authorizationService = $authorizationService;
        $this->authenticationTokenDecoder = $authenticationTokenDecoder;
    }

    public function insert(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserSectorPermissionCollection(),
                SectorTypes::Sector,
                PermissionTypes::Create
            );

            $body = $request->getParsedBody();

            $missingBodyKeys = ArrayKeysExistanceChecker::checkAndReturnMissingKeys(
                $body,
                ["name", "is_active", "value"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $name = $body["name"];
            $isActive = $body["is_active"];
            $value = $body["value"];

            $sector = $this->sectorService->insert(
                new Sector(
                    Name::make($name),
                    SectorValue::make($value),
                    $isActive
                )
            );

            $data = [
                "id" => $sector->getIdValue(),
                "name" => $sector->getNameValue(),
                "is_active" => $sector->getIsActive(),
                "value" => $sector->getSectorValue()
            ];

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "data" => $data
                    ])
                );
            return $response->withStatus(201);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserSectorPermissionCollection(),
                SectorTypes::Sector,
                PermissionTypes::Update
            );

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $body = $request->getParsedBody();

            $missingBodyKeys = ArrayKeysExistanceChecker::checkAndReturnMissingKeys(
                $body,
                ["name", "is_active", "value"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $id = (int)$args["id"];
            $name = $body["name"];
            $isActive = $body["is_active"];
            $value = $body["value"];

            $sector = new Sector(
                Name::make($name),
                SectorValue::make($value),
                $isActive
            );
            $sector->setId(Id::make($id));

            $wasUpdated = $this->sectorService->update(
                $sector
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "status" => $wasUpdated ? "updated" : "same"
                    ])
                );
            return $response
                ->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserSectorPermissionCollection(),
                SectorTypes::Sector,
                PermissionTypes::Activate
            );

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $body = $request->getParsedBody();

            $missingBodyKeys = ArrayKeysExistanceChecker::checkAndReturnMissingKeys(
                $body,
                ["is_active"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $id = (int)$args["id"];
            $isActive = $body["is_active"];

            $wasUpdated = $this->sectorService->setIsActive(
                Id::make($id),
                $isActive
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "status" => $wasUpdated ? "updated" : "same"
                    ])
                );
            return $response
                ->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserSectorPermissionCollection(),
                SectorTypes::Sector,
                PermissionTypes::List
            );

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $id = (int)$args["id"];

            $sector = $this->sectorService->findById(
                Id::make($id)
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "data" => [
                            "id" => $sector->getIdValue(),
                            "name" => $sector->getNameValue(),
                            "value" => $sector->getSectorValue(),
                            "is_active" => $sector->getIsActive()
                        ]
                    ])
                );
            return $response
                ->withStatus(200);
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserSectorPermissionCollection(),
                SectorTypes::Sector,
                PermissionTypes::List
            );

            $sectors = $this->sectorService->findAll();

            if ($sectors->count() === 0) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "message" => "Nothing found!"
                        ])
                    );
                return $response->withStatus(404);
            }

            foreach ($sectors->fetchAll() as $sector) {
                $data[] = [
                    "id" => $sector->getIdValue(),
                    "name" => $sector->getNameValue(),
                    "value" => $sector->getSectorValue(),
                    "is_active" => $sector->getIsActive()
                ];
            }

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "number_found" => $sectors->count(),
                        "data" => $data
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
