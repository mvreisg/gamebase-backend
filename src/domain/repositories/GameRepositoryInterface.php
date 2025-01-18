<?php
    namespace Gamebase\Domain\Repositories;

    use Gamebase\Domain\Entities\Game;

    interface GameRepositoryInterface 
    {
        public function insert(Game $game): Game;
        public function edit(Game $game): bool;
        public function delete(int $id): bool;
        public function findById(int $id): Game|null;
        public function findAll(): array;
        public function hasDuplicatedNames(string $name): bool;
    }
?>