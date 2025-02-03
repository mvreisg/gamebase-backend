<?php

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;

/**
 * Platform entity class.
 */
class Platform
{
    /**
     * @var int $id The Platform id.
     */
    private int $id;

    /**
     * @var string $name The Platform name.
     */
    private string $name;

    /**
     * Platform entity class constructor.
     * @param int $id [optional] The Platform id.
     * @param string $name [optional] The Platform name.
     * @return void
     */
    public function __construct(int $id = 0, string $name = '')
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Gets the id of the Platform.
     * @return int The Platform id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id of the Platform.
     * @param int $id The Platform id.
     * @return void
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * Gets the name of the Platform.
     * @return string The name of the Platform.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name of the platform.
     * @param string $name The platform name.
     * @return void
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Validates if the id is valid.
     * @throws EntityInvalidValueException Throwed if the id is invalid.
     */
    public function validateId()
    {
        if ($this->id < 1) {
            throw new EntityInvalidValueException('O id ' . $this->id . ' é menor que um.');
        }
    }

    /**
     * Validates the name of the Platform.
     * @throws EntityInvalidValueException Throwed if the name is invalid.
     */
    public function validateName()
    {
        if ($this->name === null) {
            throw new EntityInvalidValueException('O nome é nulo.');
        }

        $this->name = trim($this->name);

        if ($this->name === '') {
            throw new EntityInvalidValueException('O nome está vazio.');
        }
    }
}
