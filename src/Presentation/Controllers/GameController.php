<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Exception;
use PDOException;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Application\Services\GameService;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpApplication;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;

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

        $body = $request->parseBodyFromJSON();
        $name = $body['name'] ?? null;

        $game = null;
        try {
            $game = $this->service->insert($name);
        } catch (EntityInvalidValueException | DatabaseDuplicatedEntryException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (PDOException | Exception $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        if ($game == false) {
            $messages[] = 'Ocorreu um erro ao inserir o jogo. Contate o suporte.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        $messages[] = 'Jogo inserido com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages
            ])
            ->status(HttpApplication::STATUS_CODES[201])
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

        $body = $request->parseBodyFromJSON();
        $params = $request->getParams();

        $gameId = $params['gameId'] ?? null;
        $name = $body['name'] ?? null;

        $wasGameEditAnSuccess = false;
        try {
            $gameId = intval($gameId);
            $wasGameEditAnSuccess = $this->service->update($gameId, $name);
        } catch (EntityInvalidValueException | DatabaseDuplicatedEntryException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (PDOException | Exception $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        if ($wasGameEditAnSuccess === false) {
            $messages[] = 'Verifique se o id do jogo existe no banco de dados.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[404])
                ->sendJSON();
            return;
        }

        $messages[] = 'Jogo editado com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages
            ])
            ->status(HttpApplication::STATUS_CODES[200])
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
        $gameId = $params['gameId'] ?? null;

        $game = null;
        try {
            $gameId = intval($gameId);
            $game = $this->service->findById($gameId);
        } catch (EntityInvalidValueException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (PDOException | Exception $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        if ($game === null) {
            $messages[] = 'O jogo procurado não existe.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[404])
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
            ->status(HttpApplication::STATUS_CODES[200])
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
                ->status(HttpApplication::STATUS_CODES[500])
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
                ->status(HttpApplication::STATUS_CODES[200])
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
            ->status(HttpApplication::STATUS_CODES[200])
            ->sendJSON();
    }
}
