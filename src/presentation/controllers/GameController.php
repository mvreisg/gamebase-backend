<?php
    namespace Gamebase\Presentation\Controllers;

    use Exception;
    use Gamebase\Application\Services\GameService;
    use Gamebase\Domain\Exceptions\InvalidValueException;
    use Gamebase\Infrastructure\Exceptions\DuplicatedEntryException;
    use Gamebase\Presentation\Http\HttpRequest;
    use Gamebase\Presentation\Http\HttpResponse;

    class GameController 
    {
        private GameService $service;

        public function __construct(GameService $service)
        {
            $this->service = $service;
        }

        public function insert(HttpRequest $request, HttpResponse $response)
        {          
            $message = [];
            
            $body = $request->parseBodyFromJSON();

            $name = $body["name"] ?? null;
            //$genresId = $body["genresIds"] ?? null;
            //$platformsId = $body["platformsIds"] ?? null;

            $hasNullKey = false;
            if ($name === null){
                $hasNullKey = true;
                $message[] = "O parâmetro 'name' não foi informado.";
            }

            /*
            if ($genresId === null){
                $hasNullKey = true;
                $message[] = "O parâmetro 'genresId' não foi informado.";
            }

            if ($platformsId === null){
                $hasNullKey = true;
                $message[] = "O parâmetro 'platformsId' não foi informado.";
            }
            */

            if ($hasNullKey){
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            try
            {
                $game = $this->service->insert($name);
                if ($game == false) 
                {
                    $message[] = "Ocorreu um erro ao inserir o jogo. Contate o suporte.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
                }

                //$gameId = $game->getId();

                //array_map(fn($genreId) => $this->gameGenreService->insert($genreId, $gameId), $genresId);
                //array_map(fn($platformId) => $this->gamePlatformService->insert($platformId, $gameId), $platformsId);

                $message[] = "Jogo inserido com sucesso!";
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_201)->sendJSON();
            }
            catch(InvalidValueException | DuplicatedEntryException $e)
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
            }
            catch (Exception $e) 
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function edit(HttpRequest $request, HttpResponse $response) 
        {
            $message = [];

            try 
            {
                $body = $request->parseBodyFromJSON();
                $params = $request->getParams();

                $gameId = $params["gameId"] ?? null;
                $name = $body["name"] ?? null;

                if ($gameId === null){
                    $message[] = "O parâmetro 'gameId' informado é nulo.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }
                    
                $isGameIdNumeric = is_numeric($gameId);
                if ($isGameIdNumeric === false)
                {
                    $message[] = "O parâmetro 'gameId' informado precisa ser um número inteiro.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $gameId = intval($gameId);

                if ($gameId <= 0)
                {
                    $message[] = "O parâmetro 'gameId' informado precisa ser um número inteiro maior que zero.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $hasNullKey = false;
                if ($name === null){
                    $hasNullKey = true;
                    $message[] = "O parâmetro 'name' não foi informado.";
                }

                if ($hasNullKey){
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $wasGameEditAnSuccess = $this->service->edit($gameId, $name);
                if ($wasGameEditAnSuccess === false) 
                {
                    $message[] = "Ocorreu um erro ao tentar editar o jogo. Contate o suporte.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
                }
                
                $message[] = "Jogo editado com sucesso!";
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch(InvalidValueException | DuplicatedEntryException $e)
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
            }
            catch (Exception $e) 
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function findById(HttpRequest $request, HttpResponse $response)
        {
            $message = [];

            try
            {
                $params = $request->getParams();
                $gameId = $params["gameId"] ?? null;

                if ($gameId === null){
                    $message[] = "O parâmetro 'gameId' informado é nulo.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }
                   
                $isGameIdNumeric = is_numeric($gameId);
                if ($isGameIdNumeric === false)
                {
                    $message[] = "O parâmetro 'gameId' informado precisa ser um número inteiro.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $gameId = intval($gameId);

                if ($gameId <= 0)
                {
                    $message[] = "O parâmetro 'gameId' informado precisa ser um número inteiro maior que zero.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $game = $this->service->findById($gameId);
                if ($game === null)
                {
                    $message[] = "O jogo procurado não existe.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_404)->sendJSON();
                    return;
                }

                $message[] = "Jogo buscado com sucesso!";
                $response->appendArray(array_merge(["message" => $message], [
                    "data" => [
                        "id" => $game->getId(),
                        "name" => $game->getName(),
                    ]
                ]))->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function findAll(HttpRequest $request, HttpResponse $response)
        {
            $message = [];

            try
            {
                $games = $this->service->findAll();
                
                $data = array_map(fn($game) => ["id" => $game->getId(), "name" => $game->getName()], $games);

                $message[] = "Jogos buscados com sucesso!";
                $response->appendArray(array_merge(["message" => $message], ["data" => $data]))->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }
    }
?>