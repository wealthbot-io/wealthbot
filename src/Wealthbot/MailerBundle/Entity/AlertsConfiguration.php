<?php

namespace Wealthbot\MailerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AlertsConfiguration
 */
class AlertsConfiguration
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $user_id;

    /**
     * @var boolean
     */
    private $is_client_portfolio_suggestion;

    /**
     * @var boolean
     */
    private $is_client_driven_account_closures;

    /**
     * @var \Wealthbot\UserBundle\Entity\User
     */
    private $user;


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
     * Set user_id
     *
     * @param integer $userId
     * @return AlertsConfiguration
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
    
        return $this;
    }

    /**
     * Get user_id
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set is_client_portfolio_suggestion
     *
     * @param boolean $isClientPortfolioSuggestion
     * @return AlertsConfiguration
     */
    public function setIsClientPortfolioSuggestion($isClientPortfolioSuggestion)
    {
        $this->is_client_portfolio_suggestion = $isClientPortfolioSuggestion;
    
        return $this;
    }

    /**
     * Get is_client_portfolio_suggestion
     *
     * @return boolean 
     */
    public function getIsClientPortfolioSuggestion()
    {
        return $this->is_client_portfolio_suggestion;
    }

    /**
     * Set is_client_driven_account_closures
     *
     * @param boolean $isClientDrivenAccountClosures
     * @return AlertsConfiguration
     */
    public function setIsClientDrivenAccountClosures($isClientDrivenAccountClosures)
    {
        $this->is_client_driven_account_closures = $isClientDrivenAccountClosures;
    
        return $this;
    }

    /**
     * Get is_client_driven_account_closures
     *
     * @return boolean 
     */
    public function getIsClientDrivenAccountClosures()
    {
        return $this->is_client_driven_account_closures;
    }

    /**
     * Set user
     *
     * @param \Wealthbot\UserBundle\Entity\User $user
     * @return AlertsConfiguration
     */
    public function setUser(\Wealthbot\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Wealthbot\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
