<?php

namespace Wealthbot\MailerBundle\Entity;

/**
 * AlertsConfiguration.
 */
class AlertsConfiguration
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $user_id;

    /**
     * @var bool
     */
    private $is_client_portfolio_suggestion;

    /**
     * @var bool
     */
    private $is_client_driven_account_closures;

    /**
     * @var \Wealthbot\UserBundle\Entity\User
     */
    private $user;

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
     * Set user_id.
     *
     * @param int $userId
     *
     * @return AlertsConfiguration
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get user_id.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set is_client_portfolio_suggestion.
     *
     * @param bool $isClientPortfolioSuggestion
     *
     * @return AlertsConfiguration
     */
    public function setIsClientPortfolioSuggestion($isClientPortfolioSuggestion)
    {
        $this->is_client_portfolio_suggestion = $isClientPortfolioSuggestion;

        return $this;
    }

    /**
     * Get is_client_portfolio_suggestion.
     *
     * @return bool
     */
    public function getIsClientPortfolioSuggestion()
    {
        return $this->is_client_portfolio_suggestion;
    }

    /**
     * Set is_client_driven_account_closures.
     *
     * @param bool $isClientDrivenAccountClosures
     *
     * @return AlertsConfiguration
     */
    public function setIsClientDrivenAccountClosures($isClientDrivenAccountClosures)
    {
        $this->is_client_driven_account_closures = $isClientDrivenAccountClosures;

        return $this;
    }

    /**
     * Get is_client_driven_account_closures.
     *
     * @return bool
     */
    public function getIsClientDrivenAccountClosures()
    {
        return $this->is_client_driven_account_closures;
    }

    /**
     * Set user.
     *
     * @param \Wealthbot\UserBundle\Entity\User $user
     *
     * @return AlertsConfiguration
     */
    public function setUser(\Wealthbot\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \Wealthbot\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
