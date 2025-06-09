<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatformService;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;
use Mvreisg\GamebaseBackend\Infrastructure\Middlewares\AuthorizationTokenRetriever;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerInvalidValueException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerOperationErrorException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use PDOException;

class GamePlatformController
{
    private GamePlatformService $service;
    private AuthenticationService $authService;

    public function __construct(GamePlatformService $service, AuthenticationService $authService)
    {
        $this->service = $service;
        $this->authService = $authService;
    }

    public function insert(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $isAuthenticated = $this->authService->validateToken($token);
            if ($isAuthenticated === false) {
                throw new AuthenticationException('Usuário não autenticado!');
            }

            $body = $request->parseBodyFromJSONString();

            $isGameIdSetted = isset($body['gameId']);
            if ($isGameIdSetted === false) {
                throw new ControllerUndefinedValueException('A chave gameId não existe ou seu valor é null!');
            }

            $isPlatformIdSetted = isset($body['platformId']);
            if ($isPlatformIdSetted === false) {
                throw new ControllerUndefinedValueException('A chave platformId não existe ou seu valor é null!');
            }

            $gameId = $body['gameId'];
            $platformId = $body['platformId'];

            $gamePlatform = $this->service->insert($platformId, $gameId);

            $data = [
                'id' => $gamePlatform->getId(),
                'platformId' => $gamePlatform->getPlatformId(),
                'gameId' => $gamePlatform->getGameId()
            ];

            $response
                ->setBody([
                    'message' => 'Vínculo entre jogo e plataforma inserido com sucesso!',
                    'data' => $data
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[201])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (AuthenticationException $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[401])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            HttpJsonParseException |
            ControllerUndefinedValueException |
            ControllerInvalidValueException |
            EntityInvalidValueException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[400])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[500])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        }
    }

    public function update(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $isAuthenticated = $this->authService->validateToken($token);
            if ($isAuthenticated === false) {
                throw new AuthenticationException('Usuário não autenticado!');
            }

            $params = $request->getParams();
            $body = $request->parseBodyFromJSONString();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new ControllerUndefinedValueException('O parâmetro id não foi informado ou seu valor é nulo!');
            }

            $isGameIdSetted = isset($body['gameId']);
            if ($isGameIdSetted === false) {
                throw new ControllerUndefinedValueException('A chave gameId não existe ou seu valor é null!');
            }

            $isPlatformIdSetted = isset($body['platformId']);
            if ($isPlatformIdSetted === false) {
                throw new ControllerUndefinedValueException('A chave platformId não existe ou seu valor é null!');
            }

            $id = $params['id'];
            $gameId = $body['gameId'];
            $platformId = $body['platformId'];

            $wasTheUpdateSuccessful = $this->service->update($id, $platformId, $gameId);
            if ($wasTheUpdateSuccessful === false) {
                $response
                    ->setBody([
                        'message' => 'Nenhuma atualização!'
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            }

            $response
                ->setBody([
                    'message' => 'Vínculos entre jogos e plataformas editados com sucesso!'
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[200])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (AuthenticationException $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[401])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            ControllerUndefinedValueException |
            ControllerInvalidValueException |
            HttpJsonParseException |
            EntityInvalidValueException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[400])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            ControllerOperationErrorException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[500])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        }
    }

    public function delete(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $isAuthenticated = $this->authService->validateToken($token);
            if ($isAuthenticated === false) {
                throw new AuthenticationException('Usuário não autenticado!');
            }

            $params = $request->getParams();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new ControllerUndefinedValueException('O parâmetro id não foi informado ou é null!');
            }

            $id = $params['id'];

            $wasDeletionSuccessful = $this->service->delete($id);
            if ($wasDeletionSuccessful === false) {
                $response
                    ->setBody([
                        'message' => 'O registro com o id ' . $id . ' não foi encontrado!'
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            }

            $response
                ->setBody([
                    'message' => 'Vínculo entre jogos e plataformas deletado com sucesso!'
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[200])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (AuthenticationException $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[401])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            ControllerUndefinedValueException |
            HttpJsonParseException |
            EntityInvalidValueException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[400])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (DatabaseStatementCreationFailureException | PDOException $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[500])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        }
    }

    public function findById(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $isAuthenticated = $this->authService->validateToken($token);
            if ($isAuthenticated === false) {
                throw new AuthenticationException('Usuário não autenticado!');
            }

            $params = $request->getParams();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new ControllerUndefinedValueException('O parâmetro id não foi informado ou seu valor é null!');
            }

            $id = $params['id'];

            $gamePlatform = $this->service->findById($id);
            if ($gamePlatform === null) {
                $response
                    ->setBody([
                        'message' => 'A busca foi concluída e nenhum valor com o id ' . $id . ' foi encontrado!',
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            }

            $response
                ->setBody([
                    'message' => 'Busca realizada com sucesso!',
                    'data' => [
                        'id' => $gamePlatform->getId(),
                        'platformId' => $gamePlatform->getPlatformId(),
                        'gameId' => $gamePlatform->getGameId()
                    ]
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[200])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (AuthenticationException $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[401])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (ControllerUndefinedValueException | EntityInvalidValueException $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[400])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[500])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        }
    }

    public function findAll(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $isAuthenticated = $this->authService->validateToken($token);
            if ($isAuthenticated === false) {
                throw new AuthenticationException('Usuário não autenticado!');
            }

            $gamePlatforms = $this->service->findAll();

            $numberOfGamePlatforms = count($gamePlatforms);
            if ($numberOfGamePlatforms === 0) {
                $response
                    ->setBody([
                        'message' => 'A busca foi realizada com sucesso mas nenhum valor foi encontrado!',
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            }

            foreach ($gamePlatforms as $gamePlatform) {
                $data[] = [
                    'id' => $gamePlatform->getId(),
                    'platformId' => $gamePlatform->getPlatformId(),
                    'gameId' => $gamePlatform->getGameId()
                ];
            }

            $response
                ->setBody([
                    'message' => 'Busca realizada com sucesso!',
                    'data' => $data
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[200])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (AuthenticationException $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[401])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[500])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        }
    }
}
