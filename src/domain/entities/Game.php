<?php
    namespace Gamebase\Domain\Entities;

    use Gamebase\Domain\Exceptions\InvalidValueException;
    
    include_once("./../src/domain/exceptions/InvalidValueException.php");

    class Game 
    {
        private int $id;
        private string $name;
        private array $genres;
        private array $platforms;

        public function __construct(int $id = 0, string $name = "", array $genres = [], array $platforms = [])
        {
            $this->id = $id;
            $this->name = $name;
            $this->genres = $genres;
            $this->platforms = $platforms;
        }

        public function getId()
        {
            return $this->id;
        }

        public function setId(int $id)
        {
            $this->id = $id;
        }

        public function getName()
        {
            return $this->name;
        }

        public function setName(string $name)
        {
            $this->name = $name;
        }

        public function getGenres()
        {
            return $this->genres;
        }

        public function setGenres(array $genres)
        {
            $this->genres = $genres;
        }
        
        public function getPlatforms()
        {
            return $this->platforms;
        }

        public function setPlatforms(array $platforms)
        {
            $this->platforms = $platforms;
        }

        public function validateName()
        {
            if ($this->name === null){
                throw new InvalidValueException("O nome é nulo.");
            }

            $this->name = trim($this->name);

            if ($this->name === ""){
                throw new InvalidValueException("O nome está vazio.");
            }
        }
    }
?>