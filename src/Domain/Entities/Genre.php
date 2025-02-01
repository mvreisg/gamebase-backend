<?php
namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
    
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
        if ($this->name === null) {
            throw new EntityInvalidValueException("O nome é nulo.");
        }

        $this->name = trim($this->name);

        if ($this->name === "") {
            throw new EntityInvalidValueException("O nome está vazio.");
        }
    }

    public function validateId()
    {
        if ($this->id < 1) {
            throw new EntityInvalidValueException("O id ".$this->id." é menor que um.");
        }
    }
}
