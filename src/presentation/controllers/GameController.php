<?php
    namespace Gamebase\Presentation\Controllers;

    use Exception;
    use Gamebase\Application\Services\GameService;
    use Gamebase\Domain\Exceptions\EntityInvalidValueException;
    use Gamebase\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
    use Gamebase\Infrastructure\Http\HttpRequest;
    use Gamebase\Infrastructure\Http\HttpResponse;

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

            $hasNullKey = false;
            if ($name === null){
                $hasNullKey = true;
                $messages[] = "O parâmetro 'name' não foi informado.";
            }

            if ($hasNullKey){
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            try
            {
                $game = $this->service->insert($name);
                if ($game == false) 
                {
                    $messages[] = "Ocorreu um erro ao inserir o jogo. Contate o suporte.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
                }

                $messages[] = "Jogo inserido com sucesso!";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_201)->sendJSON();
            }
            catch(EntityInvalidValueException | DatabaseDuplicatedEntryException $e)
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
            }
            catch (Exception $e) 
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function edit(HttpRequest $request, HttpResponse $response) 
        {
            $messages = [];

            try 
            {
                $body = $request->parseBodyFromJSON();
                $params = $request->getParams();

                $gameId = $params["gameId"] ?? null;
                $name = $body["name"] ?? null;

                if ($gameId === null){
                    $messages[] = "O parâmetro 'gameId' informado é nulo.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }
                    
                $isGameIdNumeric = is_numeric($gameId);
                if ($isGameIdNumeric === false)
                {
                    $messages[] = "O parâmetro 'gameId' informado precisa ser um número inteiro.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $gameId = intval($gameId);

                if ($gameId <= 0)
                {
                    $messages[] = "O parâmetro 'gameId' informado precisa ser um número inteiro maior que zero.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $hasNullKey = false;
                if ($name === null){
                    $hasNullKey = true;
                    $messages[] = "O parâmetro 'name' não foi informado.";
                }

                if ($hasNullKey){
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $wasGameEditAnSuccess = $this->service->edit($gameId, $name);
                if ($wasGameEditAnSuccess === false) 
                {
                    $messages[] = "Ocorreu um erro ao tentar editar o jogo. Contate o suporte.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
                }
                
                $messages[] = "Jogo editado com sucesso!";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch(EntityInvalidValueException | DatabaseDuplicatedEntryException $e)
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
            }
            catch (Exception $e) 
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function findById(HttpRequest $request, HttpResponse $response)
        {
            $messages = [];

            try
            {
                $params = $request->getParams();
                $gameId = $params["gameId"] ?? null;

                if ($gameId === null){
                    $messages[] = "O parâmetro 'gameId' informado é nulo.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }
                   
                $isGameIdNumeric = is_numeric($gameId);
                if ($isGameIdNumeric === false)
                {
                    $messages[] = "O parâmetro 'gameId' informado precisa ser um número inteiro.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $gameId = intval($gameId);

                if ($gameId <= 0)
                {
                    $messages[] = "O parâmetro 'gameId' informado precisa ser um número inteiro maior que zero.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $game = $this->service->findById($gameId);
                if ($game === null)
                {
                    $messages[] = "O jogo procurado não existe.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_404)->sendJSON();
                    return;
                }

                $messages[] = "Jogo buscado com sucesso!";
                $response->appendArray(array_merge(["messages" => $messages], [
                    "data" => [
                        "id" => $game->getId(),
                        "name" => $game->getName(),
                    ]
                ]))->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function findAll(HttpRequest $request, HttpResponse $response)
        {
            $messages = [];

            try
            {
                $games = $this->service->findAll();
                
                $data = array_map(fn($game) => ["id" => $game->getId(), "name" => $game->getName()], $games);

                $messages[] = "Jogos buscados com sucesso!";
                $response->appendArray(array_merge(["messages" => $messages], ["data" => $data]))->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }
    }
?>