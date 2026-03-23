<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Session\Login\SessionLoginInfo;
use Mvreisg\GamebaseBackend\Application\Services\Session\Login\SessionLoginStates;
use Mvreisg\GamebaseBackend\Application\Services\Session\SessionService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Exceptions\TokenCacheException;
use Mvreisg\GamebaseBackend\Domain\Entities\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityException;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Domain\Utils\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Utils\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpSessionController
{
    private SessionService $sessionService;

    public function __construct(
        SessionService $sessionService
    ) {
        $this->sessionService = $sessionService;
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $body = $request->getParsedBody();

            $missingKeys = ArrayKeysExistanceChecker::checkAndReturnMissingKeys(
                $body,
                ["username", "password", "one_week_login"]
            );
            if (count($missingKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingKeys);
            }

            $username = $body["username"];
            $password = $body["password"];
            $oneWeekLogin = $body["one_week_login"];

            $result = $this->sessionService->tryLogin(
                new SessionLoginInfo(
                    Username::make($username),
                    DecodedPassword::make($password),
                    $oneWeekLogin
                )
            );
            $state = $result->getState();
            switch ($state) {
                case SessionLoginStates::New:
                    $token = $result->getToken();
                    $oneDayInSeconds = 60 * 60 * 24;
                    $timeToExpireInSeconds = $oneWeekLogin ? $oneDayInSeconds * 7 : $oneDayInSeconds;
                    $data = [
                        "seconds_to_expire" => $timeToExpireInSeconds,
                        "token" => $token->getToken(),
                        "login_data" => $result->getData()->toSnakeCaseArray()
                    ];
                    $response
                        ->getBody()
                        ->write(
                            json_encode($data)
                        );
                    return $response
                        ->withStatus(201);
                case SessionLoginStates::Existing:
                    $token = $result->getToken();
                    $data = [
                        "token" => $token->getToken(),
                        "login_data" => $result->getData()->toSnakeCaseArray()
                    ];
                    $response
                        ->getBody()
                        ->write(
                            json_encode($data)
                        );
                    return $response
                        ->withStatus(200);
                default:
                    $response
                        ->getBody()
                        ->write(
                            json_encode([
                                "message" => "Untreated state: $state"
                            ])
                        );
                    return $response
                        ->withStatus(500);
            }
        } catch (\Throwable $e) {
            $response
                ->getBody()
                ->write(
                    json_encode([
                        "message" => $e->getMessage()
                    ])
                );
            if ($e instanceof EntityException) {
                return $response->withStatus(400);
            }
            return $response->withStatus(500);
        }
    }

    public function logoff(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $this->sessionService->tryLogoff(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "message" => "Logoff succesful"
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            $response
                ->getBody()
                ->write(
                    json_encode([
                        "message" => $e->getMessage()
                    ])
                );
            if ($e instanceof TokenCacheException) {
                return $response->withStatus(500);
            }
            return $response->withStatus(500);
        }
    }
}
