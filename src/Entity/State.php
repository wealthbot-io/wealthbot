<?php

namespace App\Entity;

/**
 * Class State
 * @package App\Entity
 */
class State
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $abbr;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return State
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set abbr.
     *
     * @param string $abbr
     *
     * @return State
     */
    public function setAbbr($abbr)
    {
        $this->abbr = $abbr;

        return $this;
    }

    /**
     * Get abbr.
     *
     * @return string
     */
    public function getAbbr()
    {
        return $this->abbr;
    }

    public function __toString()
    {
        return $this->getName().' ('.$this->getAbbr().')';
    }
}
