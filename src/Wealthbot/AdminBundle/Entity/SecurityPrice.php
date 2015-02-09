<?php

namespace Wealthbot\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SecurityPrice
 */
class SecurityPrice
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $security_id;

    /**
     * @var float
     */
    private $price;

    /**
     * @var string
     */
    private $source;

    /**
     * @var boolean
     */
    private $is_posted;

    /**
     * @var boolean
     */
    private $is_current;

    /**
     * @var \DateTime
     */
    private $datetime;

    /**
     * @var \Wealthbot\AdminBundle\Entity\Security
     */
    private $security;


    public function __construct()
    {
        $this->is_posted = true;
    }

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
     * Set security_id
     *
     * @param integer $securityId
     * @return SecurityPrice
     */
    public function setSecurityId($securityId)
    {
        $this->security_id = $securityId;
    
        return $this;
    }

    /**
     * Get security_id
     *
     * @return integer 
     */
    public function getSecurityId()
    {
        return $this->security_id;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return SecurityPrice
     */
    public function setPrice($price)
    {
        $this->price = $price;
    
        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set source
     *
     * @param string $source
     * @return SecurityPrice
     */
    public function setSource($source)
    {
        $this->source = $source;
    
        return $this;
    }

    /**
     * Get source
     *
     * @return string 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set is_posted
     *
     * @param boolean $isPosted
     * @return SecurityPrice
     */
    public function setIsPosted($isPosted)
    {
        $this->is_posted = $isPosted;
    
        return $this;
    }

    /**
     * Get is_posted
     *
     * @return boolean 
     */
    public function getIsPosted()
    {
        return $this->is_posted;
    }

    /**
     * Set is_current
     *
     * @param boolean $isCurrent
     * @return SecurityPrice
     */
    public function setIsCurrent($isCurrent)
    {
        $this->is_current = $isCurrent;
    
        return $this;
    }

    /**
     * Get is_current
     *
     * @return boolean 
     */
    public function getIsCurrent()
    {
        return $this->is_current;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     * @return SecurityPrice
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
    
        return $this;
    }

    /**
     * Get datetime
     *
     * @return \DateTime 
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set security
     *
     * @param \Wealthbot\AdminBundle\Entity\Security $security
     * @return SecurityPrice
     */
    public function setSecurity(\Wealthbot\AdminBundle\Entity\Security $security = null)
    {
        $this->security = $security;
    
        return $this;
    }

    /**
     * Get security
     *
     * @return \Wealthbot\AdminBundle\Entity\Security 
     */
    public function getSecurity()
    {
        return $this->security;
    }
}
