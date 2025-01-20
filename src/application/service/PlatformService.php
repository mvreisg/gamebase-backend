<?php
    namespace Gamebase\Application\Services;

    use Exception;
    use Gamebase\Domain\Entities\Platform;
    use Gamebase\Domain\Repositories\PlatformRepositoryInterface;
    use Gamebase\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
    
	include_once("./../src/domain/entities/Platform.php");

    class PlatformService 
    {
        private PlatformRepositoryInterface $repository;

        public function __construct(PlatformRepositoryInterface $repository){
            $this->repository = $repository;
        }

        public function insert(string $name): Platform
        {
            $platform = new Platform();
            $platform->setName($name);
            
            try
            {
                $platform->validateName();
                $validatedName = $platform->getName();
                $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
                if ($hasDuplicatedNames) 
                {
                    throw new DatabaseDuplicatedEntryException("O nome da plataforma a ser inserida já existe no banco de dados!");
                }
                $platform = $this->repository->insert($platform);
                return $platform;
            }
            catch (Exception $e)
            {
                throw $e;
            }            
        }

        public function edit(int $id, string $name): bool 
        {
            $platform = new Platform();
            $platform->setId($id);
            $platform->setName($name);
            
            try 
            {
                $platform->validateName();
                $validatedName = $platform->getName();
                $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
                if ($hasDuplicatedNames) 
                {
                    throw new DatabaseDuplicatedEntryException("O nome da plataforma a ser editada já existe no banco de dados!");
                }
                $wasItSuccessful = $this->repository->edit($platform);
                return $wasItSuccessful;
            }
            catch (Exception $e) 
            {
                throw $e;
            }
        }         

        public function findById(int $id): Platform|null
        {
            try
            {
                $platform = $this->repository->findById($id);
                return $platform;
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
                $platforms = $this->repository->findAll();
                return $platforms;
            }
            catch (Exception $e) 
            {
                throw $e;
            }
        }
    }
?>