<?php
    namespace Gamebase\Domain\Repositories;

    use Gamebase\Domain\Entities\Platform;

    interface PlatformRepositoryInterface 
    {
        public function insert(Platform $platform): Platform;        
        public function edit(Platform $platform): bool;
        public function delete(int $id): bool;
        public function findById(int $id): Platform|null;
        public function findAll(): array;
        public function hasDuplicatedNames(string $name): bool;
    }
?>