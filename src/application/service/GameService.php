<?php
    namespace Gamebase\Application\Services;

    use Exception;
    use Gamebase\Domain\Entities\Game;
    use Gamebase\Domain\Repositories\GameRepositoryInterface;
    use Gamebase\Infrastructure\Exceptions\DuplicatedEntryException;
    
	include_once("./../src/domain/entities/Game.php");
    include_once("./../src/infrastructure/exceptions/DuplicatedEntryException.php");

    class GameService 
    {
        private GameRepositoryInterface $repository;

        public function __construct(GameRepositoryInterface $repository){
            $this->repository = $repository;
        }

        public function insert(string $name): Game
        {
            $game = new Game();
            $game->setName($name);
            
            try
            {
                $game->validateName();
                $validatedName = $game->getName();
                $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
                if ($hasDuplicatedNames)
                {
                    throw new DuplicatedEntryException("O nome do jogo a ser inserido já existe no banco de dados!");
                }
                $game = $this->repository->insert($game);
                return $game;
            }
            catch (Exception $e)
            {
                throw $e;
            }            
        }

        public function edit(int $id, string $name): bool 
        {
            $game = new Game();
            $game->setId($id);
            $game->setName($name);
            
            try 
            {
                $game->validateName();
                $validatedName = $game->getName();
                $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
                if ($hasDuplicatedNames)
                {
                    throw new DuplicatedEntryException("O nome do jogo a ser editado já existe no banco de dados!");
                }
                $wasItSuccessful = $this->repository->edit($game);
                return $wasItSuccessful;
            }
            catch (Exception $e) 
            {
                throw $e;
            }
        }

        public function findById(int $id): Game|null
        {
            try 
            {
                $game = $this->repository->findById($id);
                return $game;
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