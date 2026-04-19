<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\Sector\Service\SectorService;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;
use Mvreisg\GamebaseBackend\Infrastructure\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Util\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpSectorController
{
    private SectorService $sectorService;

    public function __construct(SectorService $sectorService)
    {
        $this->sectorService = $sectorService;
    }

    public function insert(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

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
                    null,
                    Name::create($name),
                    SectorValue::create($value),
                    $isActive
                ),
                $token
            );

            $data = [
                "id" => $sector->getId()->getValue(),
                "name" => $sector->getName()->getValue(),
                "is_active" => $sector->getIsActive(),
                "value" => $sector->getSectorValue()->getValue()
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
                Id::create($id),
                Name::create($name),
                SectorValue::create($value),
                $isActive
            );

            $wasUpdated = $this->sectorService->update(
                $sector,
                $token
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

    public function setIsActive(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

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
                Id::create($id),
                $isActive,
                $token
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

    public function findById(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $id = (int)$args["id"];

            $sector = $this->sectorService->findById(
                Id::create($id),
                $token
            );

            if ($sector === null) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "found" => false
                        ])
                    );
                return $response->withStatus(404);
            }

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "data" => [
                            "id" => $sector->getId()->getValue(),
                            "name" => $sector->getName()->getValue(),
                            "value" => $sector->getSectorValue()->getValue(),
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

    public function findAll(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $sectors = $this->sectorService->findAll(
                $token
            );

            if ($sectors === null) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "number_found" => 0
                        ])
                    );
                return $response->withStatus(404);
            }

            foreach ($sectors->fetchAll() as $sector) {
                $data[] = [
                    "id" => $sector->getId()->getValue(),
                    "name" => $sector->getName()->getValue(),
                    "value" => $sector->getSectorValue()->getValue(),
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
