<?php
    namespace Gamebase\Application\Services;

    use Exception;
    use Gamebase\Domain\Entities\GamePlatform;
    use Gamebase\Domain\Repositories\GamePlatformRepositoryInterface;
    
    include_once("./../src/domain/entities/GamePlatform.php");

    class GamePlatformService 
    {
        private GamePlatformRepositoryInterface $repository;

        public function __construct(GamePlatformRepositoryInterface $repository)
        {
            $this->repository = $repository;
        }

        public function insert(int $platformId, int $gameId): GamePlatform 
        {
            $gamePlatform = new GamePlatform();
            $gamePlatform->setPlatformId($platformId);
            $gamePlatform->setGameId($gameId);

            try
            {                
                $gamePlatform = $this->repository->insert($gamePlatform);
                return $gamePlatform;
            }
            catch (Exception $e) 
            {
                throw $e;
            }
        }

        public function edit(int $platformId, int $gameId): bool 
        {
            $gamePlatform = new GamePlatform();
            $gamePlatform->setPlatformId($platformId);
            $gamePlatform->setGameId($gameId);

            try 
            {
                $wasItSuccessful = $this->repository->edit($gamePlatform);
                return $wasItSuccessful;
            }
            catch (Exception $e) 
            {
                throw $e;
            }
        }

        public function delete(int $platformId, int $gameId): bool 
        {
            $gamePlatform = new GamePlatform();
            $gamePlatform->setPlatformId($platformId);
            $gamePlatform->setGameId($gameId);

            try 
            {
                $wasItSuccessful = $this->repository->delete($gamePlatform);
                return $wasItSuccessful;
            }
            catch (Exception $e) 
            {
                throw $e;
            }
        }

        public function deleteAllByGameId(int $gameId): bool 
        {
            $gamePlatform = new GamePlatform();
            $gamePlatform->setGameId($gameId);

            try 
            {
                $wasItSuccessful = $this->repository->deleteAllByGameId($gamePlatform);
                return $wasItSuccessful;
            }
            catch (Exception $e) 
            {
                throw $e;
            }
        }

        public function findAllGamePlatformsByGameId(int $gameId): array 
        {
            try 
            {
                $gamePlatforms = $this->repository->findAllGamePlatformsByGameId($gameId);
                return $gamePlatforms;
            }
            catch (Exception $e) 
            {
                throw $e;
            }
        }

        public function intersectionBetweenGameAndGamePlatformByGameId(): array 
        {
            try 
            {
                $gamePlatforms = $this->repository->innerJoinBetweenGameAndGamePlatformByGameId();
                return $gamePlatforms;
            }
            catch (Exception $e)
            {
                throw $e;
            }
        }
    }
?>