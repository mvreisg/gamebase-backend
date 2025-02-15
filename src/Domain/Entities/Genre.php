<?php

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;

/**
 * Genre entity class.
 */
class Genre
{
    /**
     * @var int $id The Genre id.
     */
    private mixed $id;

    /**
     * @var string $name The Genre name.
     */
    private mixed $name;

    /**
     * Genre entity class constructor.
     * @param int $id [optional] The Genre id.
     * @param string $name [optional] The Genre name.
     * @return void
     */
    public function __construct(int $id = 0, string $name = '')
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Gets the Genre id.
     * @return int The Genre id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the Genre id.
     * @param int $id The genre id.
     * @return void
     */
    public function setId(mixed $id)
    {
        $this->id = $id;
    }

    /**
     * Gets the Genre name.
     * @return string The Genre name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the Genre name.
     * @param string $name The Genre name.
     * @return void
     */
    public function setName(mixed $name)
    {
        $this->name = $name;
    }

    /**
     * Method that validates the id of the Genre.
     * @throws EntityInvalidValueException Throwed if the id is invalid.
     */
    public function validateId()
    {
        if ($this->id < 1) {
            throw new EntityInvalidValueException('O id ' . $this->id . ' é menor que um.');
        }
    }

    /**
     * Method that validates the name of the Genre.
     * @throws EntityInvalidValueException Throwed if the name is invalid.
     */
    public function validateName()
    {
        if ($this->name === null) {
            throw new EntityInvalidValueException('O nome é null.');
        }

        if (is_string($this->name) === false) {
            throw new EntityInvalidValueException('O nome não é uma string.');
        }

        $this->name = trim($this->name);

        if ($this->name === '') {
            throw new EntityInvalidValueException('O nome está vazio.');
        }
    }
}
