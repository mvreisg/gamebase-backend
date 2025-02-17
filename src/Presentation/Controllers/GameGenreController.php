<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Exception;
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
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerInvalidValueException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerOperationErrorException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use PDOException;
use Throwable;

/**
 * Game Genre controller class.
 */
class GameGenreController
{
    /**
     * @var GameGenreService $service The service to be used by this controller.
     */
    private GameGenreService $service;

    /**
     * Game Genre controller class controller.
     * @param GameGenreService $service The service to be used by this controller.
     */
    public function __construct(GameGenreService $service)
    {
        $this->service = $service;
    }

    /**
     * Method that handles the HTTP request and response of a Game Genre insertion.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function insert(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $data = [];

        try {
            $body = $request->parseBodyFromJSONString();

            $isGameIdSetted = isset($body['gameId']);
            if ($isGameIdSetted === false) {
                $messages[] = 'A chave gameId não existe no JSON ou seu valor é null!';
            }

            $isGenresIdsSetted = isset($body['genresIds']);
            if ($isGenresIdsSetted === false) {
                $messages[] = 'A chave genresIds não existe no JSON ou seu valor é null!';
            }

            $bodyHaveUndefinedValues = $isGameIdSetted === false || $isGenresIdsSetted === false;
            if ($bodyHaveUndefinedValues) {
                throw new ControllerUndefinedValueException('Ocorreu um erro!');
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
        } catch (ControllerInvalidValueException | ControllerUndefinedValueException | HttpJsonParseException | EntityInvalidValueException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (DatabaseTransactionCreationFailureException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | DatabaseFetchFailureException | PDOException | Throwable $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        $messages[] = 'Vínculo entre jogo e gênero inserido com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages,
                'data' => $data
            ])
            ->status(HttpRouter::STATUS_CODES[201])
            ->sendJSON();
    }

    /**
     * Method that handles the HTTP request and response of a Game Genre update.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function update(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];

        try {
            $body = $request->parseBodyFromJSONString();
            $params = $request->getParams();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                $messages[] = 'O parâmetro id não foi informado na URL ou seu valor é null!';
            }

            $isGameIdSetted = isset($body['gameId']);
            if ($isGameIdSetted === false) {
                $messages[] = 'A chave gameId não foi informada no JSON ou seu valor é null!';
            }

            $isGenresIdsSetted = isset($body['genresIds']);
            if ($isGenresIdsSetted === false) {
                $messages[] = 'A chave genresIds não foi informada no JSON ou seu valor é null!';
            }

            $hasUndefinedValues = $isIdSetted === false || $isGameIdSetted === false || $isGenresIdsSetted === false;
            if ($hasUndefinedValues) {
                throw new ControllerUndefinedValueException('Ocorreu um erro!');
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
                    throw new ControllerOperationErrorException('Ocorreu um erro ao executar a atualização do vínculo com id ' . $id . ', gameId ' . $gameId . ' e genreId ' . $genreId);
                }
            }
        } catch (ControllerInvalidValueException | ControllerUndefinedValueException | ControllerOperationErrorException | HttpJsonParseException | EntityInvalidValueException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (DatabaseStatementCreationFailureException | PDOException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        $messages[] = 'Vínculos entre jogos e gêneros editado com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
    }

    /**
     * Method that handles the HTTP request and response of a Game Genre deletion.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function delete(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        try {
            $params = $request->getParams();
            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new ControllerUndefinedValueException('O parâmetro id não foi informado na URL ou seu valor é null!');
            }

            $id = $params['id'];

            $wasTheDeleteSuccessful = $this->service->delete($id);
            if ($wasTheDeleteSuccessful === false) {
                throw new HttpResourceNotFoundException('Vínculo com o id ' . $id . ' não encontrado!');
            }
        } catch (HttpResourceNotFoundException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[404])
                ->sendJSON();
            return;
        } catch (HttpJsonParseException | ControllerOperationErrorException | ControllerUndefinedValueException | EntityInvalidValueException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (DatabaseStatementExecutionFailureException | DatabaseStatementCreationFailureException | PDOException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        $messages[] = 'Vínculos entre jogos e gêneros deletado com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
    }

    /**
     * Method that handles the HTTP request and response of a Game Genre search by the id.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function findById(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $gameGenre = null;
        try {
            $params = $request->getParams();
            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new ControllerUndefinedValueException('O parâmetro id não foi informado na URL ou seu valor é null!');
            }

            $id = $params['id'];
            $gameGenre = $this->service->findById($id);

            if ($gameGenre === null) {
                throw new HttpResourceNotFoundException('O vínculo entre gênero e jogo procurado não existe!');
            }
        } catch (HttpResourceNotFoundException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[404])
                ->sendJSON();
            return;
        } catch (HttpJsonParseException | EntityInvalidValueException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        $gameGenreId = $gameGenre->getId();
        $gameGenreGameId = $gameGenre->getGameId();
        $gameGenreGenreId = $gameGenre->getGenreId();

        $data = [
            'id' => $gameGenreId,
            'gameId' => $gameGenreGameId,
            'genreId' => $gameGenreGenreId
        ];

        $messages[] = 'Vínculo entre gênero e jogo encontrado com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages,
                'data' => $data
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
        return;
    }

    /**
     * Method that handles the HTTP request and response of a search of all Game Genres.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function findAll(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $data = [];

        $result = null;
        try {
            $result = $this->service->findAll();
        } catch (PDOException | Exception $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        if ($result === null) {
            $messages[] = 'Os vínculos entre gêneros e jogos procurados não existem!';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        }

        foreach ($result as $gameGenre) {
            $gameGenreId = $gameGenre->getId();
            $gameGenreGameId = $gameGenre->getGameId();
            $gameGenreGenreId = $gameGenre->getGenreId();

            $data[] = [
                'id' => $gameGenreId,
                'gameId' => $gameGenreGameId,
                'genreId' => $gameGenreGenreId
            ];
        }

        $messages[] = 'Vínculo entre gênero e jogo encontrado com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages,
                'data' => $data
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
        return;
    }
}
