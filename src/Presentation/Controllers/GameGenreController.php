<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Application\Services\GameGenreService;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpResourceNotFoundException;
use Mvreisg\GamebaseBackend\Infrastructure\Http\AuthorizationTokenRetriever;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerInvalidValueException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerOperationErrorException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use PDOException;
use Throwable;

class GameGenreController
{
    private GameGenreService $service;
    private AuthenticationService $authService;

    public function __construct(GameGenreService $service, AuthenticationService $authService)
    {
        $this->service = $service;
        $this->authService = $authService;
    }

    public function insert(HttpRequest $request, HttpResponse $response)
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
                throw new ControllerUndefinedValueException('A chave gameId não existe no JSON ou seu valor é null!');
            }

            $isGenresIdsSetted = isset($body['genresIds']);
            if ($isGenresIdsSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave genresIds não existe no JSON ou seu valor é null!'
                );
            }

            $gameId = $body['gameId'];
            $genresIds = $body['genresIds'];

            $isGenresIdsIterable = is_iterable($genresIds);
            if ($isGenresIdsIterable === false) {
                throw new ControllerInvalidValueException('genresIds não é um array!');
            }

            $numberOfGenresIds = count($genresIds);
            if ($numberOfGenresIds === 0) {
                throw new ControllerInvalidValueException('O array genresIds está vazio!');
            }

            foreach ($genresIds as $genreId) {
                $gameGenre = $this->service->insert($genreId, $gameId);

                $data[] = [
                    'id' => $gameGenre->getId(),
                    'gameId' => $gameGenre->getGameId(),
                    'genreId' => $gameGenre->getGenreId()
                ];
            }

            $response
                ->appendArray([
                    'message' => 'Vínculo entre jogo e gênero inserido com sucesso!',
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
            ControllerInvalidValueException |
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
        } catch (
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException |
            Throwable $e
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

    public function update(HttpRequest $request, HttpResponse $response)
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $isAuthenticated = $this->authService->validateToken($token);
            if ($isAuthenticated === false) {
                throw new AuthenticationException('Usuário não autenticado!');
            }

            $body = $request->parseBodyFromJSONString();
            $params = $request->getParams();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'O parâmetro id não foi informado na URL ou seu valor é null!'
                );
            }

            $isGameIdSetted = isset($body['gameId']);
            if ($isGameIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave gameId não foi informada no JSON ou seu valor é null!'
                );
            }

            $isGenresIdsSetted = isset($body['genresIds']);
            if ($isGenresIdsSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave genresIds não foi informada no JSON ou seu valor é null!'
                );
            }

            $id = $params['id'];
            $gameId = $body['gameId'];
            $genresIds = $body['genresIds'];

            $isGenresIdsIterable = is_iterable($genresIds);
            if ($isGenresIdsIterable === false) {
                throw new ControllerInvalidValueException('O valor de genresIds não é um array!');
            }

            $numberOfGenresIds = count($genresIds);
            if ($numberOfGenresIds === 0) {
                throw new ControllerInvalidValueException('O array genresIds está vazio!');
            }

            foreach ($genresIds as $genreId) {
                $wasTheUpdateSuccessful = $this->service->update($id, $genreId, $gameId);
                if ($wasTheUpdateSuccessful === false) {
                    throw new HttpResourceNotFoundException('A atualização não aconteceu. Verifique se o id é válido.');
                }
            }

            $response
                ->appendArray([
                    'message' => 'Vínculos entre jogos e gêneros atualizados com sucesso!'
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
        } catch (HttpResourceNotFoundException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[404])
                ->send();
            return;
        } catch (
            ControllerInvalidValueException |
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

    public function delete(HttpRequest $request, HttpResponse $response)
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
                throw new ControllerUndefinedValueException(
                    'O parâmetro id não foi informado na URL ou seu valor é null!'
                );
            }

            $id = $params['id'];

            $wasTheDeleteSuccessful = $this->service->delete($id);
            if ($wasTheDeleteSuccessful === false) {
                throw new HttpResourceNotFoundException('Vínculo com o id ' . $id . ' não encontrado!');
            }

            $response
                ->appendArray([
                    'message' => 'Vínculos entre jogos e gêneros deletado com sucesso!'
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
        } catch (HttpResourceNotFoundException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[404])
                ->send();
            return;
        } catch (HttpJsonParseException | ControllerUndefinedValueException | EntityInvalidValueException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->send();
            return;
        } catch (
            DatabaseStatementExecutionFailureException |
            DatabaseStatementCreationFailureException |
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

    public function findById(HttpRequest $request, HttpResponse $response)
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
                throw new ControllerUndefinedValueException(
                    'O parâmetro id não foi informado na URL ou seu valor é null!'
                );
            }

            $id = $params['id'];

            $gameGenre = $this->service->findById($id);

            if ($gameGenre === null) {
                throw new HttpResourceNotFoundException('O vínculo entre gênero e jogo procurado não existe!');
            }

            $response
                ->appendArray([
                    'message' => 'Vínculo entre gênero e jogo encontrado com sucesso!',
                    'data' => [
                        'id' => $gameGenre->getId(),
                        'gameId' => $gameGenre->getGameId(),
                        'genreId' => $gameGenre->getGenreId()
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
        } catch (HttpResourceNotFoundException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[404])
                ->send();
            return;
        } catch (ControllerUndefinedValueException | HttpJsonParseException | EntityInvalidValueException $e) {
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

    public function findAll(HttpRequest $request, HttpResponse $response)
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $isAuthenticated = $this->authService->validateToken($token);
            if ($isAuthenticated === false) {
                throw new AuthenticationException('Usuário não autenticado!');
            }

            $gameGenres = $this->service->findAll();

            $numberOfGameGenres = count($gameGenres);
            if ($numberOfGameGenres === 0) {
                throw new HttpResourceNotFoundException('Os vínculos entre gêneros e jogos procurados não existem!');
            }

            foreach ($gameGenres as $gameGenre) {
                $data[] = [
                    'id' => $gameGenre->getId(),
                    'gameId' => $gameGenre->getGameId(),
                    'genreId' => $gameGenre->getGenreId()
                ];
            }

            $response
                ->appendArray([
                    'message' => 'Vínculo entre gênero e jogo encontrado com sucesso!',
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
        } catch (HttpResourceNotFoundException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[404])
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
