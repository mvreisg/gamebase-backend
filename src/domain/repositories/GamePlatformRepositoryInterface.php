<?php
    namespace Mvreisg\GamebaseBackend\Domain\Repositories;

    use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;
    interface GamePlatformRepositoryInterface 
    {
        public function insert(GamePlatform $gamePlatform): GamePlatform;        
        public function edit(GamePlatform $gamePlatform): bool;
        public function delete(GamePlatform $gamePlatform): bool;
        public function deleteAllByGameId(GamePlatform $gamePlatform): bool;
        public function findAllGamePlatformsByGameId(int $gameId): array;
        public function innerJoinBetweenGameAndGamePlatformByGameId(): array; 
    }
?>