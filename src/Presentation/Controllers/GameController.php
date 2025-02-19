<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

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
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerOperationErrorException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;

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
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                $messages[] = 'A chave isActive não foi informada ou seu valor é null!';
            }

            $hasUndefinedValues = $isNameFieldSetted === false || $isIsActiveFieldSetted === false;
            if ($hasUndefinedValues) {
                throw new ControllerUndefinedValueException('Ocorreu um erro!');
            }

            $name = $body['name'];
            $isActive = $body['isActive'];
            $game = $this->service->insert($name, $isActive);
        } catch (ControllerUndefinedValueException | HttpJsonParseException | EntityInvalidValueException | DatabaseDuplicatedEntryException $e) {
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

            $isIsActiveSetted = isset($body['isActive']);
            if ($isIsActiveSetted === false) {
                $messages[] = 'A chave isActive não foi definida no JSON ou seu valor é null!';
            }

            $hasUndefinedKeys = $isGameIdSetted === false || $isNameSetted === false || $isIsActiveSetted === false;
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
            $isActive = $body['isActive'];
            $wasTheUpdateSuccessful = $this->service->update($gameId, $name, $isActive);
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

    public function setIsActive(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        try {
            $params = $request->getParams();
            $body = $request->parseBodyFromJSONString();

            $isIdSetted = isset($params['gameId']);
            if ($isIdSetted === false) {
                $messages[] = 'O parâmetro gameId não foi informado ou seu valor é null!';
            }

            $isIsActiveSetted = isset($body['isActive']);
            if ($isIsActiveSetted === false) {
                $messages[] = 'A chave isActive não foi definida no JSON ou seu valor é null!';
            }

            $hasUndefinedValues = $isIdSetted === false || $isIsActiveSetted === false;
            if ($hasUndefinedValues) {
                throw new ControllerUndefinedValueException('Ocorreu um erro!');
            }

            $id = $params['gameId'];
            $isActive = $body['isActive'];

            $wasTheUpdateSuccessful = $this->service->setIsActive($id, $isActive);
            if ($wasTheUpdateSuccessful === false) {
                throw new ControllerOperationErrorException('Ocorreu um erro ao alterar o estado do jogo com o id ' . $id);
            }

            $messages[] = 'Estado atualizado com sucesso!';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->sendJSON();
            return;
        } catch (ControllerUndefinedValueException | HttpJsonParseException | EntityInvalidValueException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (ControllerOperationErrorException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }
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
            'isActive' => $game->getIsActive()
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

        $numberOfGamesFound = count($games);
        if ($numberOfGamesFound === 0) {
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
            $gameIsActive = $game->getIsActive();

            $data[] = [
                'id' => $gameId,
                'name' => $gameName,
                'isActive' => $gameIsActive
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
