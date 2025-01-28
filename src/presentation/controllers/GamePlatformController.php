<?php
    namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

    use Exception;
    use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
    use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
    use Mvreisg\GamebaseBackend\Application\Services\GamePlatformService;

    class GamePlatformController 
    {
        private GamePlatformService $service;

        public function __construct(GamePlatformService $service) 
        {
            $this->service = $service;
        }

        public function insert(HttpRequest $request, HttpResponse $response)
        {          
            $messages = [];
            
            $body = $request->parseBodyFromJSON();
            $params = $request->getParams();

            $gameId = $params["gameId"] ?? null;
            $platformsIds = $body["platformsIds"] ?? null;

            $hasErrors = false;
            if ($gameId === null)
            {
                $hasErrors = true;
                $messages[] = "O parâmetro 'gameId' não foi informado.";
            }

            if ($platformsIds === null)
            {
                $hasErrors = true;
                $messages[] = "O parâmetro 'platformsIds' não foi informado.";
            }

            if ($hasErrors) 
            {
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            $isGameIdNumeric = is_numeric($gameId);
            if ($isGameIdNumeric === false) 
            {
                $messages[] = "O valor de 'gameId' precisa ser numérico.";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            $gameId = intval($gameId);

            if ($gameId <= 0) 
            {
                $messages[] = "O valor de 'gameId' precisa ser maior que zero.";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            try
            {
                foreach ($platformsIds as $platformId) 
                {
                    if ($platformId === null) 
                    {
                        $messages[] = "Um dos ids de 'genresId' é nulo.";
                        $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                        return;
                    }

                    $isPlatformIdNumeric = is_numeric($platformId);
                    if ($isPlatformIdNumeric === false) 
                    {
                        $messages[] = "Um dos ids de 'genresId' não é um número inteiro.";
                        $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                        return;
                    }

                    $platformId = intval($platformId);

                    $gamePlatform = $this->service->insert($platformId, $gameId);
                    if ($gamePlatform == false) 
                    {
                        $messages[] = "Ocorreu um erro ao inserir o vínculo entre jogo e plataforma. Contate o suporte.";
                        $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
                    }
                }

                $messages[] = "Vínculo entre jogo e plataforma inserido com sucesso!";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_201)->sendJSON();
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
                $platformsIds = $body["platformsIds"] ?? null;

                $hasNullKeys = false;
                if ($gameId === null)
                {
                    $hasNullKeys = true;
                    $messages[] = "É necessário informar o id do jogo na rota.";                    
                }

                if ($platformsIds === null)
                {
                    $hasNullKeys = true;
                    $messages[] = "É necessário informar os ids dos gêneros em um array 'platformIds'.";
                }

                if ($hasNullKeys)
                {
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

                $hasValuesToBeEdited = count($platformsIds);
                if ($hasValuesToBeEdited === false) 
                {
                    $messages[] = "Não há valores a serrem editados!";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_200)->sendJSON();
                    return;
                }

                foreach ($platformsIds as $platformId) 
                {
                    if ($platformId === null) 
                    {
                        $messages[] = "Um dos ids de gênero informado é nulo.";
                        $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    }

                    $isPlatformIdNumeric = is_numeric($platformId);
                    if ($isPlatformIdNumeric === false) 
                    {
                        $messages[] = "Um dos ids de gênero não é um número inteiro.";
                        $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    }
                }

                $gamePlatforms = $this->service->findAllGamePlatformsByGameId($gameId);                
                $gamePlatformsIds = array_map(fn($gamePlatform) => $gamePlatform->getPlatformId(), $gamePlatforms);   

                if (count($gamePlatformsIds) === 0 && count($platformsIds) === 0)
                {
                    $messages[] = "Nenhuma alteração feita!";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_200)->sendJSON();
                    return;
                }
                else if (count($gamePlatformsIds) === 0 && count($platformsIds) > 0) 
                {
                    array_map(fn($genreId) => $this->service->insert($genreId, $gameId), $platformsIds);
                }                         
                else if (count($gamePlatformsIds) > 0 && count($platformsIds) > 0) 
                {
                    $mergedPlatformsIds = array_merge($gamePlatformsIds, $platformsIds);   
                    $includentPlatformsIds = array_filter($mergedPlatformsIds, fn($value) => in_array($value, $gamePlatformsIds) === false);
                    $excludentPlatformsIds = array_filter($mergedPlatformsIds, fn($value) => in_array($value, $platformsIds) === false);
                    array_map(fn($genreId) => $this->service->insert($genreId, $gameId), $includentPlatformsIds);
                    array_map(fn($genreId) => $this->service->delete($genreId, $gameId), $excludentPlatformsIds);
                }

                $messages[] = "Vínculos entre jogos e plataformas editados com sucesso!";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function findAllPlatformsIdsByGameId(HttpRequest $request, HttpResponse $response) 
        {
            $messages = [];

            try 
            {
                $params = $request->getParams();
                $gameId = $params["gameId"] ?? null;

                if ($gameId === null) 
                {
                    $messages[] = "O id do jogo não foi informado.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $isGameIdNumeric = is_numeric($gameId);
                if ($isGameIdNumeric === false) 
                {
                    $messages[] = "O id do jogo não é um número.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $gameId = intval($gameId);

                if ($gameId <= 0) 
                {
                    $messages[] = "O id do jogo precisa ser maior que zero.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $gamePlatforms = $this->service->findAllGamePlatformsByGameId($gameId);                
                $data = array_map(fn($gamePlatform) => $gamePlatform->getPlatformId(), $gamePlatforms); 

                $messages[] = "Ids de plataformas buscados com sucesso!";
                $response->appendArray(["messages" => $messages, "data" => $data])->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function deleteAllPlatformsByGameId(HttpRequest $request, HttpResponse $response) 
        {
            $messages = [];

            try 
            {
                $params = $request->getParams();
                $gameId = $params["gameId"] ?? null;

                if ($gameId === null) 
                {
                    $messages[] = "O id do jogo não foi informado.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $isGameIdNumeric = is_numeric($gameId);
                if ($isGameIdNumeric === false) 
                {
                    $messages[] = "O id do jogo não é um número.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $gameId = intval($gameId);

                if ($gameId <= 0) 
                {
                    $messages[] = "O id do jogo precisa ser maior que zero.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $wasItSuccessful = $this->service->deleteAllByGameId($gameId);
                if ($wasItSuccessful === false) 
                {
                    $messages[] = "Ocorreu um erro ao deletar os vínculos entre jogo e plataforma pelo id do jogo. Contate o suporte.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
                    return;    
                }

                $messages[] = "Vínculos entre jogo e plataforma baseados no id do jogo deletados com sucesso!";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }
    }
?>