<?php
    namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

    use Exception;
    use Mvreisg\GamebaseBackend\Application\Services\GenreService;
    use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
    use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
    use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
    use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;

    class GenreController 
    {
        private GenreService $service;

        public function __construct(GenreService $service)
        {
            $this->service = $service;
        }

        public function insert(HttpRequest $request, HttpResponse $response)
        {
            $messages = [];

            $body = $request->parseBodyFromJSON();
            $name = $body["name"] ?? null;

            if ($name === null)
            {
                $messages[] = "O parâmetro 'name' não foi informado no JSON.";                
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            $genre = null;
            try
            {
                $genre = $this->service->insert($name);
            }
            catch(EntityInvalidValueException | DatabaseDuplicatedEntryException $e)
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }
            catch (Exception $e) 
            {
                $messages = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
                return;
            }
            
            if ($genre == false) 
            {
                $messages[] = "Ocorreu um erro ao inserir o gênero. Contate o suporte.";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
                return;
            }

            $messages[] = "Gênero incluído com sucesso!";
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_201)->sendJSON();
        }

        public function edit(HttpRequest $request, HttpResponse $response)
        {
            $messages = [];
            
            $body = $request->parseBodyFromJSON();
            $params = $request->getParams();

            $genreId = $params["genreId"] ?? null;
            $name = $body["name"] ?? null;

            $hasParameterError = false;                
            if ($genreId === null)
            {
                $hasParameterError = true;
                $messages[] = "O id do gênero não foi informado na URL.";
            }
            
            if ($name === null){
                $hasParameterError = true;
                $messages[] = "O parâmetro 'name' não foi informado no JSON.";
            }

            if ($hasParameterError){
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            $isGenreIdNumeric = is_numeric($genreId);
            if ($isGenreIdNumeric === false)
            {
                $messages[] = "O id do gênero precisa ser um número inteiro.";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            $genreId = intval($genreId);
            if ($genreId <= 0)
            {
                $messages[] = "O id do gênero precisa ser un número inteiro maior que zero.";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            $wasEditAnSuccess = false;
            try 
            {
                $wasEditAnSuccess = $this->service->edit($genreId, $name);
            }
            catch(EntityInvalidValueException | DatabaseDuplicatedEntryException $e)
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }
            catch (Exception $e) 
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
                return;
            }

            if ($wasEditAnSuccess === false) 
            {
                $messages[] = "Verifique se o id do gênero existe no banco de dados.";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            $messages[] = "Gênero editado com sucesso!";
            $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_200)->sendJSON();
        }

        public function findById(HttpRequest $request, HttpResponse $response)
        {
            $messages = [];
            $data = [];

            $params = $request->getParams();

            $genreId = $params["genreId"] ?? null;

            if ($genreId === null)
            {
                $messages[] = "O id do gênero não foi informado na URL.";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            $isGenreIdNumeric = is_numeric($genreId);
            if ($isGenreIdNumeric === false)
            {
                $messages[] = "O id do gênero precisa ser um número inteiro.";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            $genreId = intval($genreId);
            if ($genreId <= 0)
            {
                $messages[] = "O id do gênero precisa ser un número inteiro maior que zero.";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            $genre = null;
            try
            {
                $genre = $this->service->findById($genreId);
            }
            catch (Exception $e) 
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
                return;
            }

            if ($genre === null)
            {
                $messages[] = "O gênero procurado não existe!";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            $genreId = $genre->getId();
            $genreName = $genre->getName();

            $data = [
                "id" => $genreId,
                "name" => $genreName,
            ];

            $messages[] = "Gênero buscado com sucesso!";
            $response->appendArray(["messages" => $messages, "data" => $data])->status(HTTP_STATUS_CODE_200)->sendJSON();
        }

        public function findAll(HttpRequest $request, HttpResponse $response)
        {
            $messages = [];
            $data = [];

            $genres = null;
            try
            {
                $genres = $this->service->findAll();
            }
            catch (Exception $e) 
            {
                $messages[] = $e->getMessage();
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_500)->sendJSON();
                return;
            }              

            $numberOfGenres = count($genres);
            if ($numberOfGenres === 0) 
            {
                $messages[] = "A busca foi concluída e nenhum gênero foi encontrado.";
                $response->appendArray(["messages" => $messages])->status(HTTP_STATUS_CODE_200)->sendJSON();
                return;
            }

            foreach ($genres as $genre) 
            {
                $genreId = $genre->getId();
                $genreName = $genre->getName();

                $data[] = [
                    "id" => $genreId,
                    "name" => $genreName
                ];
            }

            $messages[] = "Gêneros buscados com sucesso!";
            $response->appendArray(["messages" => $messages, "data" => $data])->status(HTTP_STATUS_CODE_200)->sendJSON();
        }
    }
?>