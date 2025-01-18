<?php
    namespace Gamebase\Presentation\Controllers;

    use Exception;
    use Gamebase\Application\Services\GenreService;
    use Gamebase\Domain\Exceptions\InvalidValueException;
    use Gamebase\Infrastructure\Exceptions\DuplicatedEntryException;
    use Gamebase\Presentation\Http\HttpRequest;
    use Gamebase\Presentation\Http\HttpResponse;

    class GenreController 
    {
        private GenreService $service;

        public function __construct(GenreService $service)
        {
            $this->service = $service;
        }

        public function insert(HttpRequest $request, HttpResponse $response)
        {
            $message = [];

            $body = $request->parseBodyFromJSON();

            $name = $body["name"] ?? null;

            $hasParameterError = false;
            if ($name === null){
                $hasParameterError = true;
                $message[] = "O parâmetro 'name' não foi informado.";                
            }

            if ($hasParameterError){
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            try
            {
                $genre = $this->service->insert($name);
                if ($genre == false) 
                {
                    $message[] = "Ocorreu algum erro ao inserir o gênero. Contate o suporte.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
                    return;
                }

                $message[] = "Gênero incluído com sucesso!";
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_201)->sendJSON();
            }
            catch(InvalidValueException | DuplicatedEntryException $e)
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
            }
            catch (Exception $e) 
            {
                $message = $e->getMessage();
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

                $genreId = $params["genreId"] ?? null;
                $name = $body["name"] ?? null;

                $hasParameterError = false;                
                if ($genreId === null){
                    $message[] = "O parâmetro 'genreId' informado é nulo.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }                
                    
                $isGenreIdNumeric = is_numeric($genreId);
                if ($isGenreIdNumeric === false)
                {
                    $message[] = "O parâmetro 'genreId' informado precisa ser um número inteiro.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $genreId = intval($genreId);

                if ($genreId <= 0)
                {
                    $message[] = "O parâmetro 'genreId' informado precisa ser un número inteiro maior que zero.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                if ($name === null){
                    $hasParameterError = true;
                    $message[] = "O parâmetro 'name' não foi informado.";
                }
    
                if ($hasParameterError){
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $wasEditAnSuccess = $this->service->edit($genreId, $name);
                if ($wasEditAnSuccess === false) 
                {
                    $message[] = "Ocorreu algum erro ao editar o gênero. Contate o suporte.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
                    return;
                }

                $message[] = "Gênero editado com sucesso!";
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_200)->sendJSON();
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

                $genreId = $params["genreId"] ?? null;

                if ($genreId === null){
                    $message[] = "O parâmetro da rota 'genreId' não foi informado.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $isGenreIdNumeric = is_numeric($genreId);
                if ($isGenreIdNumeric === false)
                {
                    $message[] = "O parâmetro 'genreId' informado precisa ser um número inteiro.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $genreId = intval($genreId);

                if ($genreId <= 0)
                {
                    $message[] = "O parâmetro 'genreId' informado precisa ser un número inteiro maior que zero.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }
    
                $genre = $this->service->findById($genreId);
                if ($genre === null)
                {
                    $message[] = "O gênero procurado não existe!";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $genreId = $genre->getId();
                $genreName = $genre->getName();

                $message[] = "Genero buscado com sucesso!";
                $response->appendArray(array_merge(["message" => $message], [
                    "data" => [
                        "id" => $genreId,
                        "name" => $genreName,
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
                $genres = $this->service->findAll();
                if ($genres == false) 
                {
                    $message[] = "Ocorreu algum erro ao buscar os gêneros. Contate o suporte.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
                    return;
                }                

                $numberOfGenres = count($genres);
                if ($numberOfGenres === 0) 
                {
                    $message[] = "A busca foi concluída e nenhum gênero foi encontrado.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_200)->sendJSON();
                    return;
                }

                $data = [];
                foreach ($genres as $genre) 
                {
                    $genreId = $genre->getId();
                    $genreName = $genre->getName();

                    $data[] = [
                        "id" => $genreId,
                        "name" => $genreName
                    ];
                }

                $message[] = "Gêneros buscados com sucesso!";
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