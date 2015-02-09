<?php

namespace Wealthbot\RiaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RiaDashboardBox
 */
class RiaDashboardBox
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $ria_user_id;

    /**
     * @var string
     */
    private $template;

    /**
     * @var integer
     */
    private $sequence;

    /**
     * @var \Wealthbot\UserBundle\Entity\User
     */
    private $ria;

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
     * Set ria_user_id
     *
     * @param integer $riaUserId
     * @return RiaDashboardBox
     */
    public function setRiaUserId($riaUserId)
    {
        $this->ria_user_id = $riaUserId;
    
        return $this;
    }

    /**
     * Get ria_user_id
     *
     * @return integer 
     */
    public function getRiaUserId()
    {
        return $this->ria_user_id;
    }

    /**
     * Set template
     *
     * @param string $template
     * @return RiaDashboardBox
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    
        return $this;
    }

    /**
     * Get template
     *
     * @return string 
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set sequence
     *
     * @param integer $sequence
     * @return RiaDashboardBox
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
     * Set ria
     *
     * @param \Wealthbot\UserBundle\Entity\User $ria
     * @return RiaDashboardBox
     */
    public function setRia(\Wealthbot\UserBundle\Entity\User $ria = null)
    {
        $this->ria = $ria;
    
        return $this;
    }

    /**
     * Get ria
     *
     * @return \Wealthbot\UserBundle\Entity\User 
     */
    public function getRia()
    {
        return $this->ria;
    }
}
