<?php

namespace Wealthbot\ClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClosingAccountMessage
 */
class ClosingAccountMessage
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $message;


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
     * Set message
     *
     * @param string $message
     * @return ClosingAccountMessage
     */
    public function setMessage($message)
    {
        $this->message = $message;
    
        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }
}
