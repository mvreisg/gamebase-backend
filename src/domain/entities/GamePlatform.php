<?php
    namespace Mvreisg\GamebaseBackend\Domain\Entities;

    class GamePlatform 
    {
        private int $id;
        private int $platformId;
        private int $gameId;

        public function __construct(int $id = 0, int $platformId = 0, int $gameId = 0)
        {
            $this->id = $id;
            $this->platformId = $platformId;
            $this->gameId = $gameId;
        }

        public function getId()
        {
            return $this->id;
        }

        public function setId(int $id)
        {
            $this->id = $id;
        }

        public function getPlatformId()
        {
            return $this->platformId;
        }

        public function setPlatformId(int $platformId)
        {
            $this->platformId = $platformId;
        }

        public function getGameId()
        {
            return $this->gameId;
        }

        public function setGameId(int $gameId)
        {
            $this->gameId = $gameId;
        }
    }
?>