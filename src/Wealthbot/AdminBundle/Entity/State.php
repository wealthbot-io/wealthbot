<?php

namespace Wealthbot\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wealthbot\AdminBundle\Entity\State
 */
class State
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $abbr
     */
    private $abbr;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return State
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set abbr
     *
     * @param string $abbr
     * @return State
     */
    public function setAbbr($abbr)
    {
        $this->abbr = $abbr;
    
        return $this;
    }

    /**
     * Get abbr
     *
     * @return string 
     */
    public function getAbbr()
    {
        return $this->abbr;
    }

    public function __toString()
    {
        return $this->getName() . ' (' .$this->getAbbr() . ')';
    }
}
