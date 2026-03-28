<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\User\UserService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Entities\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Domain\Utils\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Utils\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpUserController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function insert(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $body = $request->getParsedBody();

            $missingBodyKeys = ArrayKeysExistanceChecker::checkAndReturnMissingKeys(
                $body,
                ["username", "password", "is_active"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $queryParams = $request->getQueryParams();
            $showPassword = false;
            if (isset($queryParams["show_password"])) {
                $showPassword = $queryParams["show_password"] === "true";
            }

            $username = $body["username"];
            $password = $body["password"];
            $isActive = $body["is_active"];

            $user = $this->userService->insert(
                new User(
                    Username::make($username),
                    DecodedPassword::make($password),
                    $isActive
                ),
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $data = [
                "id" => $user->getId()->getValue(),
                "username" => $user->getUsername()->getValue(),
                "isActive" => $user->getIsActive()
            ];

            if ($showPassword) {
                $data["password"] = $user->getPassword()->getValue();
            }

            $response
                ->getBody()
                ->write(
                    json_encode($data)
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
                ["username", "password", "is_active"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $id = (int)$args["id"];
            $username = $body["username"];
            $password = $body["password"];
            $isActive = $body["is_active"];

            $user = new User(
                Username::make($username),
                DecodedPassword::make($password),
                $isActive
            );
            $user->setId(Id::make($id));

            $wasUpdated = $this->userService->update(
                $user,
                new EncodedAuthenticationToken(
                    $token
                )
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

            $wasUpdated = $this->userService->setIsActive(
                Id::make($id),
                $isActive,
                new EncodedAuthenticationToken(
                    $token
                )
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

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $id = (int)$args["id"];

            $queryParams = $request->getQueryParams();
            $showPassword = false;
            if (isset($queryParams["show_password"])) {
                $showPassword = $queryParams["show_password"] === "true";
            }

            $user = $this->userService->findById(
                Id::make($id),
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $data = [
                "id" => $user->getId()->getValue(),
                "username" => $user->getUsername()->getValue(),
                "isActive" => $user->getIsActive()
            ];

            if ($showPassword) {
                $data["password"] = $user->getPassword()->getValue();
            }

            $response
                ->getBody()
                ->write(
                    json_encode($data)
                );
            return $response->withStatus(201);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findByUsername(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["username"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $username = $args["username"];

            $queryParams = $request->getQueryParams();
            $showPassword = false;
            if (isset($queryParams["show_password"])) {
                $showPassword = $queryParams["show_password"] === "true";
            }

            $user = $this->userService->findByUsername(
                Username::make($username),
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $data = [
                "id" => $user->getId()->getValue(),
                "username" => $user->getUsername()->getValue(),
                "isActive" => $user->getIsActive()
            ];

            if ($showPassword) {
                $data["password"] = $user->getPassword()->getValue();
            }

            $response
                ->getBody()
                ->write(
                    json_encode($data)
                );
            return $response->withStatus(201);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $users = $this->userService->findAll(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            if ($users->count() === 0) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "message" => "Nothing found!"
                        ])
                    );
                return $response->withStatus(404);
            }

            $queryParams = $request->getQueryParams();
            $showPassword = false;
            if (isset($queryParams["show_password"])) {
                $showPassword = $queryParams["show_password"] === "true";
            }

            $data = [];
            foreach ($users->fetchAll() as $user) {
                $value = [
                    "id" => $user->getId()->getValue(),
                    "username" => $user->getUsername()->getValue(),
                    "isActive" => $user->getIsActive()
                ];

                if ($showPassword) {
                    $value["password"] = $user->getPassword()->getValue();
                }

                $data[] = $value;
            }

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "number_found" => $users->count(),
                        "data" => $data
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
