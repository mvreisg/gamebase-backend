<?php
    namespace Gamebase\Presentation\Controllers;

    use Exception;
    use Gamebase\Infrastructure\Http\HttpRequest;
    use Gamebase\Infrastructure\Http\HttpResponse;
    use Gamebase\Application\Services\GameGenreService;

    class GameGenreController 
    {
        private GameGenreService $service;

        public function __construct(GameGenreService $service) 
        {
            $this->service = $service;
        }

        public function insert(HttpRequest $request, HttpResponse $response)
        {          
            $message = [];
            
            $body = $request->parseBodyFromJSON();
            $params = $request->getParams();

            $gameId = $params["gameId"] ?? null;
            $genresIds = $body["genresIds"] ?? null;

            $hasErrors = false;
            if ($gameId === null)
            {
                $hasErrors = true;
                $message[] = "O parâmetro 'gameId' não foi informado.";
            }

            if ($genresIds === null)
            {
                $hasErrors = true;
                $message[] = "O parâmetro 'genresId' não foi informado.";
            }

            if ($hasErrors) 
            {
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            $isGameIdNumeric = is_numeric($gameId);
            if ($isGameIdNumeric === false) 
            {
                $message[] = "O valor de 'gameId' precisa ser numérico.";
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            $gameId = intval($gameId);

            if ($gameId <= 0) 
            {
                $message[] = "O valor de 'gameId' precisa ser maior que zero.";
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            try
            {
                foreach ($genresIds as $genreId) 
                {
                    if ($genreId === null) 
                    {
                        $message[] = "Um dos ids de 'genresId' é nulo.";
                        $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                        return;
                    }

                    $isGenreIdNumeric = is_numeric($genreId);
                    if ($isGenreIdNumeric === false) 
                    {
                        $message[] = "Um dos ids de 'genresId' não é um número inteiro.";
                        $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                        return;
                    }

                    $genreId = intval($genreId);

                    $gameGenre = $this->service->insert($genreId, $gameId);
                    if ($gameGenre == false) 
                    {
                        $message[] = "Ocorreu um erro ao inserir o vínculo entre jogo e gênero. Contate o suporte.";
                        $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
                    }
                }

                $message[] = "Vínculo entre jogo e gênero inserido com sucesso!";
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_201)->sendJSON();
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
                $genresIds = $body["genresIds"] ?? null;

                $hasNullKeys = false;
                if ($gameId === null)
                {
                    $hasNullKeys = true;
                    $message[] = "É necessário informar o id do jogo na rota.";                    
                }

                if ($genresIds === null)
                {
                    $hasNullKeys = true;
                    $message[] = "É necessário informar os ids dos gêneros em um array 'genresIds'.";
                }

                if ($hasNullKeys)
                {
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

                $hasValuesToBeEdited = count($genresIds);
                if ($hasValuesToBeEdited === false) 
                {
                    $message[] = "Não há valores a serrem editados!";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_200)->sendJSON();
                    return;
                }

                foreach ($genresIds as $genreId) 
                {
                    if ($genreId === null) 
                    {
                        $message[] = "Um dos ids de gênero informado é nulo.";
                        $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    }

                    $isGenreIdNumeric = is_numeric($genreId);
                    if ($isGenreIdNumeric === false) 
                    {
                        $message[] = "Um dos ids de gênero não é um número inteiro.";
                        $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    }
                }

                $existingGameGenres = $this->service->findAllGameGenresByGameId($gameId);                
                $existingGenresId = array_map(fn($existingGameGenre) => $existingGameGenre->getGenreId(), $existingGameGenres);   

                if (count($existingGenresId) === 0 && count($genresIds) === 0)
                {
                    $message[] = "Nenhuma alteração feita!";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_200)->sendJSON();
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

                $message[] = "Vínculos entre jogos e gêneros editados com sucesso!";
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function findAllGenresIdByGameId(HttpRequest $request, HttpResponse $response) 
        {
            $message = [];

            try 
            {
                $params = $request->getParams();
                $gameId = $params["gameId"] ?? null;

                if ($gameId === null) 
                {
                    $message[] = "O id do jogo não foi informado.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $isGameIdNumeric = is_numeric($gameId);
                if ($isGameIdNumeric === false) 
                {
                    $message[] = "O id do jogo não é um número.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $gameId = intval($gameId);

                if ($gameId <= 0) 
                {
                    $message[] = "O id do jogo precisa ser maior que zero.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $gameGenres = $this->service->findAllGameGenresByGameId($gameId);                
                $data = array_map(fn($gameGenre) => $gameGenre->getGenreId(), $gameGenres); 

                $message[] = "Ids de gêneros buscados com sucesso!";
                $response->appendArray(["message" => $message, "data" => $data])->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function deleteAllGenresByGameId(HttpRequest $request, HttpResponse $response) 
        {
            $message = [];

            try 
            {
                $params = $request->getParams();
                $gameId = $params["gameId"] ?? null;

                if ($gameId === null) 
                {
                    $message[] = "O id do jogo não foi informado.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $isGameIdNumeric = is_numeric($gameId);
                if ($isGameIdNumeric === false) 
                {
                    $message[] = "O id do jogo não é um número.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $gameId = intval($gameId);

                if ($gameId <= 0) 
                {
                    $message[] = "O id do jogo precisa ser maior que zero.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $wasItSuccessful = $this->service->deleteAllByGameId($gameId);
                if ($wasItSuccessful === false) 
                {
                    $message[] = "Ocorreu um erro ao deletar os vínculos entre jogo e gênero pelo id do jogo. Contate o suporte.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
                    return;    
                }

                $message[] = "Vínculos entre jogo e gênero baseados no id do jogo deletados com sucesso!";
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }
    }
?>