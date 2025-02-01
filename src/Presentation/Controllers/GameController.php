<?php
namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Exception;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Application\Services\GameService;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;

class GameController
{
    private GameService $service;

    public function __construct(GameService $service)
    {
        $this->service = $service;
    }

    public function insert(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
            
        $body = $request->parseBodyFromJSON();
        $name = $body["name"] ?? null;

        if ($name === null) {
            $messages[] = "O parâmetro 'name' não foi informado na URL.";
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
            return;
        }

        $game = null;
        try {
            $game = $this->service->insert($name);
        } catch (EntityInvalidValueException | DatabaseDuplicatedEntryException $e) {
            $messages[] = $e->getMessage();
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
            return;
        } catch (Exception $e) {
            $messages[] = $e->getMessage();
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
            return;
        }

        if ($game == false) {
            $messages[] = "Ocorreu um erro ao inserir o jogo. Contate o suporte.";
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
            return;
        }

        $messages[] = "Jogo inserido com sucesso!";
        $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_201)->sendJSON();
    }

    public function edit(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];

        $body = $request->parseBodyFromJSON();
        $params = $request->getParams();

        $gameId = $params["gameId"] ?? null;
        $name = $body["name"] ?? null;

        $hasNullKey = false;
        if ($name === null) {
            $hasNullKey = true;
            $messages[] = "O parâmetro 'name' não foi informado na URL.";
        }
            
        if ($gameId === null) {
            $hasNullKey = true;
            $messages[] = "O id do jogo não foi informado na URL.";
        }

        if ($hasNullKey) {
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
            return;
        }
                
        $isGameIdNumeric = is_numeric($gameId);
        if ($isGameIdNumeric === false) {
            $messages[] = "O parâmetro 'gameId' informado precisa ser um número inteiro.";
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
            return;
        }

        $gameId = intval($gameId);
        if ($gameId <= 0) {
            $messages[] = "O parâmetro 'gameId' informado precisa ser um número inteiro maior que zero.";
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
            return;
        }

        $wasGameEditAnSuccess = false;
        try {
            $wasGameEditAnSuccess = $this->service->edit($gameId, $name);
        } catch (EntityInvalidValueException | DatabaseDuplicatedEntryException $e) {
            $messages[] = $e->getMessage();
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
            return;
        } catch (Exception $e) {
            $messages[] = $e->getMessage();
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
            return;
        }

        if ($wasGameEditAnSuccess === false) {
            $messages[] = "Verifique se o id do jogo existe no banco de dados.";
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
            return;
        }
            
        $messages[] = "Jogo editado com sucesso!";
        $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_200)->sendJSON();
    }

    public function findById(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $data = [];

        $params = $request->getParams();
        $gameId = $params["gameId"] ?? null;

        if ($gameId === null) {
            $messages[] = "O id do jogo não foi informado na URL.";
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
            return;
        }
               
        $isGameIdNumeric = is_numeric($gameId);
        if ($isGameIdNumeric === false) {
            $messages[] = "O id do jogo precisa ser um número inteiro.";
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
            return;
        }

        $gameId = intval($gameId);
        if ($gameId <= 0) {
            $messages[] = "O id do jogo precisa ser um número inteiro maior que zero.";
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
            return;
        }

        $game = null;
        try {
            $game = $this->service->findById($gameId);
        } catch (Exception $e) {
            $messages[] = $e->getMessage();
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
            return;
        }

        if ($game === null) {
            $messages[] = "O jogo procurado não existe.";
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_404)->sendJSON();
            return;
        }

        $data = [
            "id" => $game->getId(),
            "name" => $game->getName(),
        ];

        $messages[] = "Jogo buscado com sucesso!";
        $response->appendArray(["messages" => $messages, "data" => $data])->status(HTTP_STATUS_CODE_200)->sendJSON();
    }

    public function findAll(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];

        $games = null;
        try {
            $games = $this->service->findAll();
        } catch (Exception $e) {
            $messages[] = $e->getMessage();
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
            return;
        }

        $numberOfGames = count($games);
        if ($numberOfGames === 0) {
            $messages[] = "A busca foi concluída e nenhum jogo foi encontrado.";
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_200)->sendJSON();
            return;
        }

        foreach ($games as $game) {
            $gameId = $game->getId();
            $gameName = $game->getName();

            $data[] = [
                "id" => $gameId,
                "name" => $gameName
            ];
        }

        $messages[] = "Jogos buscados com sucesso!";
        $response->appendArray(["messages" => $messages, "data" => $data])->status(HTTP_STATUS_CODE_200)->sendJSON();
    }
}
