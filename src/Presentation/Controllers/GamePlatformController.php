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
use Mvreisg\GamebaseBackend\Infrastructure\Http\AuthorizationTokenRetriever;
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
                ->appendArray([
                    'message' => 'Vínculo entre jogo e plataforma inserido com sucesso!',
                    'data' => $data
                ])
                ->status(HttpRouter::STATUS_CODES[201])
                ->send();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->send();
            return;
        } catch (
            HttpJsonParseException |
            ControllerUndefinedValueException |
            ControllerInvalidValueException |
            EntityInvalidValueException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->send();
            return;
        } catch (
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->send();
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
                    ->appendArray([
                        'message' => 'Nenhuma atualização!'
                    ])
                    ->status(HttpRouter::STATUS_CODES[200])
                    ->send();
                return;
            }

            $response
                ->appendArray([
                    'message' => 'Vínculos entre jogos e plataformas editados com sucesso!'
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->send();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->send();
            return;
        } catch (
            ControllerUndefinedValueException |
            ControllerInvalidValueException |
            HttpJsonParseException |
            EntityInvalidValueException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->send();
            return;
        } catch (
            ControllerOperationErrorException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->send();
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
                    ->appendArray([
                        'message' => 'O registro com o id ' . $id . ' não foi encontrado!'
                    ])
                    ->status(HttpRouter::STATUS_CODES[200])
                    ->send();
                return;
            }

            $response
                ->appendArray([
                    'message' => 'Vínculo entre jogos e plataformas deletado com sucesso!'
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->send();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->send();
            return;
        } catch (
            ControllerUndefinedValueException |
            HttpJsonParseException |
            EntityInvalidValueException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->send();
            return;
        } catch (DatabaseStatementCreationFailureException | PDOException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->send();
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
                    ->appendArray([
                        'message' => 'A busca foi concluída e nenhum valor com o id ' . $id . ' foi encontrado!',
                    ])
                    ->status(HttpRouter::STATUS_CODES[200])
                    ->send();
                return;
            }

            $response
                ->appendArray([
                    'message' => 'Busca realizada com sucesso!',
                    'data' => [
                        'id' => $gamePlatform->getId(),
                        'platformId' => $gamePlatform->getPlatformId(),
                        'gameId' => $gamePlatform->getGameId()
                    ]
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->send();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->send();
            return;
        } catch (ControllerUndefinedValueException | EntityInvalidValueException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->send();
            return;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->send();
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
                    ->appendArray([
                        'message' => 'A busca foi realizada com sucesso mas nenhum valor foi encontrado!',
                    ])
                    ->status(HttpRouter::STATUS_CODES[200])
                    ->send();
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
                ->appendArray([
                    'message' => 'Busca realizada com sucesso!',
                    'data' => $data
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->send();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->send();
            return;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->send();
            return;
        }
    }
}
