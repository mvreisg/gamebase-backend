<?php
    namespace Gamebase\Application\Services;

    use Exception;
    use Gamebase\Domain\Entities\Genre;
    use Gamebase\Domain\Repositories\GenreRepositoryInterface;
    use Gamebase\Infrastructure\Exceptions\DuplicatedEntryException;
    use Gamebase\Infrastructure\Utils\Pathfinder;

    include_once(PATHFINDER_DIRECTORY);
	include_once(Pathfinder::find("src/domain/entities/Genre.php"));

    class GenreService 
    {
        private GenreRepositoryInterface $repository;

        public function __construct(GenreRepositoryInterface $repository){
            $this->repository = $repository;
        }

        public function insert(string $name): Genre
        {
            $genre = new Genre();
            $genre->setName($name);
            
            try
            {
                $genre->validateName();
                $validatedName = $genre->getName();
                $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
                if ($hasDuplicatedNames)
                {
                    throw new DuplicatedEntryException("O nome do gênero a ser inserido já existe no banco de dados!");
                }
                $genre = $this->repository->insert($genre);
                return $genre;
            }
            catch (Exception $e)
            {
                throw $e;
            }            
        }

        public function edit(int $id, string $name): bool 
        {
            $genre = new Genre();
            $genre->setId($id);
            $genre->setName($name);
            
            try 
            {
                $genre->validateName();
                $validatedName = $genre->getName();
                $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
                if ($hasDuplicatedNames)
                {
                    throw new DuplicatedEntryException("O nome do gênero a ser editado já existe no banco de dados!");
                }
                $wasItSuccessful = $this->repository->edit($genre);
                return $wasItSuccessful;
            }
            catch (Exception $e) 
            {
                throw $e;
            }
        }        

        public function findById(int $id): Genre|null 
        {
            try
            {
                $genre = $this->repository->findById($id);
                return $genre;
            }
            catch (Exception $e)
            {
                throw $e;
            }
        }

        public function findAll(): array 
        {
            try
            {
                $games = $this->repository->findAll();
                return $games;
            }
            catch (Exception $e)
            {
                throw $e;
            }
        }
    }
?>