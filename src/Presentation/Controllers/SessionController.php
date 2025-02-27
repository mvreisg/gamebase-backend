<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Exception;
use Mvreisg\GamebaseBackend\Application\Services\SessionService;
use Mvreisg\GamebaseBackend\Application\Services\UserService;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpForbiddenException;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use PDOException;

class SessionController
{
    private SessionService $sessionService;
    private UserService $userService;

    public function __construct(SessionService $sessionService, UserService $userService)
    {
        $this->sessionService = $sessionService;
        $this->userService = $userService;
    }

    public function handleSessionStart(HttpRequest $request, HttpResponse $response)
    {
        try {
            $params = $request->getParams();
            $isUserIdSetted = isset($params['userId']);
            if ($isUserIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave userId não foi definida no JSON ou seu valor é null!'
                );
            }

            $userId = $params['userId'];

            $jwtToken = $this->sessionService->generateToken($userId);

            $response
                ->appendArray([
                    'token' => $jwtToken
                ])
                ->status(200)
                ->send();
            return;
        } catch (
            ControllerUndefinedValueException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (Exception $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }
    }

    public function handleSessionValidation(HttpRequest $request, HttpResponse $response)
    {
        try {
            $body = $request->parseBodyFromJSONString();
            $isTokenSetted = isset($body['token']);
            if ($isTokenSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave userId não foi definida no JSON ou seu valor é null!'
                );
            }

            $token = $body['token'];

            $userId = $this->sessionService->validateToken($token);

            $user = $this->userService->findById($userId);
            if ($user === null) {
                throw new HttpForbiddenException('O token é inválido!');
            }

            $response
                ->status(HttpRouter::STATUS_CODES[200])
                ->sendJSON();
            return;
        } catch (
            HttpForbiddenException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[403])
                ->sendJSON();
            return;
        } catch (
            EntityInvalidValueException |
            ControllerUndefinedValueException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (
            DatabaseFetchFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException |
            Exception $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }
    }
}
