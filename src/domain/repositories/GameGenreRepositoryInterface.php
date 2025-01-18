<?php
    namespace Gamebase\Domain\Repositories;

    use Gamebase\Domain\Entities\GameGenre;

    interface GameGenreRepositoryInterface 
    {
        public function insert(GameGenre $gameGenre): GameGenre;        
        public function edit(GameGenre $gameGenre): bool;
        public function delete(GameGenre $gameGenre): bool;
        public function deleteAllByGameId(GameGenre $gameGenre): bool;
        public function findAllGameGenresByGameId(int $gameId): array;
        public function innerJoinBetweenGameAndGameGenreByGameId(): array;           
    }
?>