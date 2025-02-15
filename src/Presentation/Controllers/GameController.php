<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Exception;
use PDOException;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Application\Services\GameService;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;

/**
 * The Game controller class.
 */
class GameController
{
    /**
     * @var GameService $service The service to be used by the controller.
     */
    private GameService $service;

    /**
     * The Game controller class constructor.
     * @param GameService $service The service to be used by the controller.
     */
    public function __construct(GameService $service)
    {
        $this->service = $service;
    }

    /**
     * Method that handles the HTTP request and response of a Game insertion.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function insert(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];

        $game = null;
        try {
            $body = $request->parseBodyFromJSONString();
            $isNameFieldSetted = isset($body['name']);
            if ($isNameFieldSetted === false) {
                $messages[] = 'A chave name não foi informada ou seu valor é null!';
                $response
                    ->appendArray([
                        'messages' => $messages
                    ])
                    ->status(HttpRouter::STATUS_CODES[400])
                    ->sendJSON();
                return;
            }

            $name = $body['name'];
            $game = $this->service->insert($name);
        } catch (HttpJsonParseException | EntityInvalidValueException | DatabaseDuplicatedEntryException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | DatabaseFetchFailureException | PDOException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        if ($game == false) {
            $messages[] = 'Ocorreu um erro ao inserir o jogo. Contate o suporte.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        $messages[] = 'Jogo inserido com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages
            ])
            ->status(HttpRouter::STATUS_CODES[201])
            ->sendJSON();
    }

    /**
     * Method that handles the HTTP request and response of a Game update.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     */
    public function update(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $wasTheUpdateSuccessful = false;
        try {
            $body = $request->parseBodyFromJSONString();
            $params = $request->getParams();

            $isGameIdSetted = isset($params['gameId']);
            if ($isGameIdSetted === false) {
                $messages[] = 'A chave gameId não existe ou seu valor é null!';
            }

            $isNameSetted = isset($body['name']);
            if ($isNameSetted === false) {
                $messages[] = 'A chave name não existe ou seu valor é null!';
            }

            $hasUndefinedKeys = $isGameIdSetted === false || $isNameSetted === false;
            if ($hasUndefinedKeys) {
                $response
                    ->appendArray([
                        'messages' => $messages
                    ])
                    ->status(HttpRouter::STATUS_CODES[400])
                    ->sendJSON();
                return;
            }

            $gameId = $params['gameId'];
            $name = $body['name'];
            $wasTheUpdateSuccessful = $this->service->update($gameId, $name);
        } catch (HttpJsonParseException | DatabaseDuplicatedEntryException | EntityInvalidValueException $e) {
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

        if ($wasTheUpdateSuccessful === false) {
            $messages[] = 'Os dados do jogo não puderam ser atualizados.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[404])
                ->sendJSON();
            return;
        }

        $messages[] = 'Dados do jogo atualizado com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
    }

    /**
     * Method that handles the HTTP request and response of a Game being found by the id.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP resposne object.
     */
    public function findById(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $data = [];

        $params = $request->getParams();

        $isGameIdSetted = isset($params['gameId']);
        if ($isGameIdSetted === false) {
            $messages[] = 'O id do jogo não foi informado na URL!';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        }

        $gameId = $params['gameId'];
        $game = null;
        try {
            $game = $this->service->findById($gameId);
        } catch (EntityInvalidValueException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (DatabaseFetchFailureException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        if ($game === null) {
            $messages[] = 'O registro com o gameId ' . $gameId . ' não foi encontrado.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[404])
                ->sendJSON();
            return;
        }

        $data = [
            'id' => $game->getId(),
            'name' => $game->getName(),
        ];

        $messages[] = 'Jogo buscado com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages,
                'data' => $data
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
    }

    /**
     * Method that handles the HTTP request and response of a search for all Game registers.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP resposne object.
     */
    public function findAll(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];

        $games = null;
        try {
            $games = $this->service->findAll();
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

        $numberOfGames = count($games);
        if ($numberOfGames === 0) {
            $messages[] = 'A busca foi concluída e nenhum jogo foi encontrado.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->sendJSON();
            return;
        }

        foreach ($games as $game) {
            $gameId = $game->getId();
            $gameName = $game->getName();

            $data[] = [
                'id' => $gameId,
                'name' => $gameName
            ];
        }

        $messages[] = 'Jogos buscados com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages,
                'data' => $data
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
    }
}
