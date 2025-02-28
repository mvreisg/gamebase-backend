<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\SessionException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpForbiddenException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\UserService;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\EncryptionErrorException;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use PDOException;

class AuthenticationController
{
    private AuthenticationService $authService;
    private UserService $userService;

    public function __construct(AuthenticationService $authService, UserService $userService)
    {
        $this->authService = $authService;
        $this->userService = $userService;
    }

    public function handleLogin(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $body = $request->parseBodyFromJSONString();

            $isUserNameFieldSetted = isset($body['username']);
            if ($isUserNameFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave username não existe ou seu valor é null!');
            }

            $isPassWordFieldSetted = isset($body['password']);
            if ($isPassWordFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave password não existe ou seu valor é null!');
            }

            $userName = $body['username'];
            $passWord = $body['password'];

            $hasCredentials = $this->authService->checkIfItHasCredentials($userName, $passWord);

            $user = $this->userService->findByUserName($userName);
            $userId = $user->getId();

            if ($hasCredentials) {
                $jwtToken = $this->authService->generateToken($userId);

                $response
                    ->appendArray([
                        'token' => $jwtToken
                    ])
                    ->status(HttpRouter::STATUS_CODES[200])
                    ->send();
                return;
            }
            $response
                ->appendArray([
                    'message' => 'Verifique seu nome de usuário ou senha!'
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->sendJSON();
            return;
        } catch (
            ControllerUndefinedValueException |
            EntityInvalidValueException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (
            EncryptionErrorException |
            DatabaseFetchFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
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

    public function handleValidation(HttpRequest $request, HttpResponse $response)
    {
        try {
            $body = $request->parseBodyFromJSONString();
            $isTokenSetted = isset($body['token']);
            if ($isTokenSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave token não foi definida no JSON ou seu valor é null!'
                );
            }

            $token = $body['token'];

            $userId = $this->authService->validateToken($token);

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
            SessionException |
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
            PDOException $e
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
