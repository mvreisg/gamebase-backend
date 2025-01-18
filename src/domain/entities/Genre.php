<?php
    namespace Gamebase\Domain\Entities;

    use Gamebase\Domain\Exceptions\InvalidValueException;
    use Gamebase\Infrastructure\Utils\Pathfinder;

    include_once(PATHFINDER_DIRECTORY);
    include_once(Pathfinder::find("src/domain/exceptions/InvalidValueException.php"));

    class Genre 
    {
        private int $id;
        private string $name;

        public function __construct(int $id = 0, string $name = "")
        {
            $this->id = $id;
            $this->name = $name;
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