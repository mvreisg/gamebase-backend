<?php
    namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

    use Exception;
    use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
    use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
    use Mvreisg\GamebaseBackend\Application\Services\GameGenreService;

    class GameGenreController 
    {
        private GameGenreService $service;

        public function __construct(GameGenreService $service) 
        {
            $this->service = $service;
        }

        public function insert(HttpRequest $request, HttpResponse $response)
        {          
            $messages = [];
            
            $body = $request->parseBodyFromJSON();
            $params = $request->getParams();

            $gameId = $params["gameId"] ?? null;
            $genresIds = $body["genresIds"] ?? null;

            $hasErrors = false;
            if ($gameId === null)
            {
                $hasErrors = true;
                $messages[] = "O id do jogo não foi informado na URL.";
            }

            if ($genresIds === null)
            {
                $hasErrors = true;
                $messages[] = "O array de ids de gêneros não foi informado no JSON.";
            }

            if ($hasErrors) 
            {
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            $isGameIdNumeric = is_numeric($gameId);
            if ($isGameIdNumeric === false) 
            {
                $messages[] = "O id do jogo precisa ser um número.";
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
        
            foreach ($genresIds as $genreId) 
            {
                if ($genreId === null) 
                {
                    $messages[] = "Um dos ids de gênero é nulo.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $isGenreIdNumeric = is_numeric($genreId);
                if ($isGenreIdNumeric === false) 
                {
                    $messages[] = "Um dos ids de gênero não é um número inteiro.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $genreId = intval($genreId);
                if ($genreId <= 0) 
                {
                    $messages[] = "Um dos ids de gênero não é um número inteiro maior que zero.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                try
                {
                    $gameGenre = $this->service->insert($genreId, $gameId);
                }
                catch (Exception $e) 
                {
                    $messages[] = $e->getMessage();
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
                    return;
                }

                if ($gameGenre == false) 
                {
                    $messages[] = "Ocorreu um erro ao inserir o vínculo entre jogo e gênero. Contate o suporte.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
                    return;
                }
            }

            $messages[] = "Vínculo entre jogo e gênero inserido com sucesso!";
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_201)->sendJSON();
        }

        public function edit(HttpRequest $request, HttpResponse $response) 
        {
            $messages = [];

            try 
            {
                $body = $request->parseBodyFromJSON();
                $params = $request->getParams();

                $gameId = $params["gameId"] ?? null;
                $genresIds = $body["genresIds"] ?? null;

                $hasNullKeys = false;
                if ($gameId === null)
                {
                    $hasNullKeys = true;
                    $messages[] = "É necessário informar o id do jogo na rota.";                    
                }

                if ($genresIds === null)
                {
                    $hasNullKeys = true;
                    $messages[] = "É necessário informar os ids dos gêneros em um array 'genresIds'.";
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

                $hasValuesToBeEdited = count($genresIds);
                if ($hasValuesToBeEdited === false) 
                {
                    $messages[] = "Não há valores a serrem editados!";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_200)->sendJSON();
                    return;
                }

                foreach ($genresIds as $genreId) 
                {
                    if ($genreId === null) 
                    {
                        $messages[] = "Um dos ids de gênero informado é nulo.";
                        $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    }

                    $isGenreIdNumeric = is_numeric($genreId);
                    if ($isGenreIdNumeric === false) 
                    {
                        $messages[] = "Um dos ids de gênero não é um número inteiro.";
                        $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    }
                }

                $existingGameGenres = $this->service->findAllGameGenresByGameId($gameId);                
                $existingGenresId = array_map(fn($existingGameGenre) => $existingGameGenre->getGenreId(), $existingGameGenres);   

                if (count($existingGenresId) === 0 && count($genresIds) === 0)
                {
                    $messages[] = "Nenhuma alteração feita!";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_200)->sendJSON();
                    return;
                }
                else if (count($existingGenresId) === 0 && count($genresIds) > 0) 
                {
                    array_map(fn($genreId) => $this->service->insert($genreId, $gameId), $genresIds);
                }                         
                else if (count($existingGenresId) > 0 && count($genresIds) > 0) 
                {
                    $mergedGenresIds = array_merge($existingGenresId, $genresIds);   
                    $includentGenresIds = array_filter($mergedGenresIds, fn($value) => in_array($value, $existingGenresId) === false);
                    $excludentGenresIds = array_filter($mergedGenresIds, fn($value) => in_array($value, $genresIds) === false);
                    array_map(fn($genreId) => $this->service->insert($genreId, $gameId), $includentGenresIds);
                    array_map(fn($genreId) => $this->service->delete($genreId, $gameId), $excludentGenresIds);
                }

                $messages[] = "Vínculos entre jogos e gêneros editados com sucesso!";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function findAllGenresIdByGameId(HttpRequest $request, HttpResponse $response) 
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

                $gameGenres = $this->service->findAllGameGenresByGameId($gameId);                
                $data = array_map(fn($gameGenre) => $gameGenre->getGenreId(), $gameGenres); 

                $messages[] = "Ids de gêneros buscados com sucesso!";
                $response->appendArray(["messages" => $messages, "data" => $data])->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function deleteAllGenresByGameId(HttpRequest $request, HttpResponse $response) 
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
                    $messages[] = "Ocorreu um erro ao deletar os vínculos entre jogo e gênero pelo id do jogo. Contate o suporte.";
                    $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
                    return;    
                }

                $messages[] = "Vínculos entre jogo e gênero baseados no id do jogo deletados com sucesso!";
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