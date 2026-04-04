<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\User\Service\UserService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Decoded\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;
use Mvreisg\GamebaseBackend\Infrastructure\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Util\Response\HttpMissingKeysInformerResponse;
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
                User::create(
                    null,
                    Username::create($username),
                    DecodedPassword::make($password),
                    $isActive
                ),
                $token
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
                Id::create($id),
                Username::create($username),
                DecodedPassword::make($password),
                $isActive
            );

            $wasUpdated = $this->userService->update(
                $user,
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
                Id::create($id),
                $token
            );

            if ($user === null) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "found" => false
                        ])
                    );
                return $response->withStatus(404);
            }

            $data = [
                "id" => $user->getId()->getValue(),
                "username" => $user->getUsername()->getValue(),
                "is_active" => $user->getIsActive()
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
                Username::create($username),
                $token
            );

            if ($user === null) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "found" => false
                        ])
                    );
                return $response->withStatus(404);
            }

            $data = [
                "id" => $user->getId()->getValue(),
                "username" => $user->getUsername()->getValue(),
                "is_active" => $user->getIsActive()
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
                $token
            );

            if ($users === null) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "number_found" => 0
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
                    "is_active" => $user->getIsActive()
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
