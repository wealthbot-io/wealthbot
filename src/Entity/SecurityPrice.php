<?php

namespace App\Entity;

/**
 * Class SecurityPrice
 * @package App\Entity
 */
class SecurityPrice
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
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
     * @var bool
     */
    private $is_posted;

    /**
     * @var bool
     */
    private $is_current;

    /**
     * @var \DateTime
     */
    private $datetime;

    /**
     * @param \App\Entity\Security
     */
    private $security;

    public function __construct()
    {
        $this->is_posted = true;
    }

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
     * Set security_id.
     *
     * @param int $securityId
     *
     * @return SecurityPrice
     */
    public function setSecurityId($securityId)
    {
        $this->security_id = $securityId;

        return $this;
    }

    /**
     * Get security_id.
     *
     * @return int
     */
    public function getSecurityId()
    {
        return $this->security_id;
    }

    /**
     * Set price.
     *
     * @param float $price
     *
     * @return SecurityPrice
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set source.
     *
     * @param string $source
     *
     * @return SecurityPrice
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set is_posted.
     *
     * @param bool $isPosted
     *
     * @return SecurityPrice
     */
    public function setIsPosted($isPosted)
    {
        $this->is_posted = $isPosted;

        return $this;
    }

    /**
     * Get is_posted.
     *
     * @return bool
     */
    public function getIsPosted()
    {
        return $this->is_posted;
    }

    /**
     * Set is_current.
     *
     * @param bool $isCurrent
     *
     * @return SecurityPrice
     */
    public function setIsCurrent($isCurrent)
    {
        $this->is_current = $isCurrent;

        return $this;
    }

    /**
     * Get is_current.
     *
     * @return bool
     */
    public function getIsCurrent()
    {
        return $this->is_current;
    }

    /**
     * Set datetime.
     *
     * @param \DateTime $datetime
     *
     * @return SecurityPrice
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * Get datetime.
     *
     * @return \DateTime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set security.
     *
     * @param \App\Entity\Security $security
     *
     * @return SecurityPrice
     */
    public function setSecurity(Security $security = null)
    {
        $this->security = $security;

        return $this;
    }

    /**
     * Get security.
     *
     * @return \App\Entity\Security
     */
    public function getSecurity()
    {
        return $this->security;
    }
}
