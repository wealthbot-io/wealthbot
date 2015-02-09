<?php

namespace Wealthbot\RiaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wealthbot\RiaBundle\Entity\RiskQuestion
 */
class RiskQuestion
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var string $description
     */
    private $description = 'description';

    /**
     * @var integer $sequence
     */
    private $sequence = 100;

    /**
     * @var boolean $is_withdraw_age_input
     */
    private $is_withdraw_age_input = false;

    /**
     * @var integer $owner_id
     */
    private $owner_id;

    /**
     * @var Wealthbot\UserBundle\Entity\User
     */
    private $owner;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return RiskQuestion
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set title
     *
     * @param string $title
     * @return RiskQuestion
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return RiskQuestion
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set sequence
     *
     * @param integer $sequence
     * @return RiskQuestion
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    
        return $this;
    }

    /**
     * Get sequence
     *
     * @return integer 
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $answers;
    
    /**
     * Add answers
     *
     * @param Wealthbot\RiaBundle\Entity\RiskAnswer $answers
     * @return RiskQuestion
     */
    public function addAnswer(\Wealthbot\RiaBundle\Entity\RiskAnswer $answers)
    {
        $this->answers[] = $answers;
    
        return $this;
    }

    /**
     * Remove answers
     *
     * @param Wealthbot\RiaBundle\Entity\RiskAnswer $answers
     */
    public function removeAnswer(\Wealthbot\RiaBundle\Entity\RiskAnswer $answers)
    {
        $this->answers->removeElement($answers);
    }

    /**
     * Get answers
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Set is_withdraw_age_input
     *
     * @param boolean $isWithdrawAgeInput
     * @return RiskQuestion
     */
    public function setIsWithdrawAgeInput($isWithdrawAgeInput)
    {
        $this->is_withdraw_age_input = $isWithdrawAgeInput;
    
        return $this;
    }

    /**
     * Get is_withdraw_age_input
     *
     * @return boolean 
     */
    public function getIsWithdrawAgeInput()
    {
        return $this->is_withdraw_age_input;
    }

    /**
     * Set owner_id
     *
     * @param integer $ownerId
     * @return RiskQuestion
     */
    public function setOwnerId($ownerId)
    {
        $this->owner_id = $ownerId;
    
        return $this;
    }

    /**
     * Get owner_id
     *
     * @return integer 
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * Set owner
     *
     * @param Wealthbot\UserBundle\Entity\User $owner
     * @return RiskQuestion
     */
    public function setOwner(\Wealthbot\UserBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;
    
        return $this;
    }

    /**
     * Get owner
     *
     * @return Wealthbot\UserBundle\Entity\User 
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
