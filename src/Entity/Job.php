<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class Job
 * @package App\Entity
 */
class Job
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
     * @var \DateTime
     */
    private $started_at;

    /**
     * @var \DateTime
     */
    private $finished_at;

    /**
     * @var bool
     */
    private $is_error;

    /**
     * @param \App\Entity\User
     */
    private $user;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $rebalancerActions;

    /**
     * @var int
     */
    private $rebalance_type;

    const JOB_NAME_REBALANCER = 'rebalancer';

    const REBALANCE_TYPE_FULL = 0;
    const REBALANCE_TYPE_REQUIRED_CASH = 1;
    const REBALANCE_TYPE_FULL_AND_TLH = 2;
    const REBALANCE_TYPE_NO_ACTIONS = 3;
    const REBALANCE_TYPE_INITIAL = 4;

    public function __construct()
    {
        $this->started_at = new \DateTime();
        $this->is_error = false;
        $this->rebalancerActions = new ArrayCollection();
    }

    public static function rebalanceTypeChoicesForSelect()
    {
        return [
            self::REBALANCE_TYPE_FULL => 'Full Rebalance',
            self::REBALANCE_TYPE_REQUIRED_CASH => 'Required Cash',
            self::REBALANCE_TYPE_FULL_AND_TLH => 'Full Rebalance & Tax Loss Harvest',
        ];
    }

    public static function rebalanceTypeChoices()
    {
        return [
            self::REBALANCE_TYPE_FULL => 'Full Rebalance',
            self::REBALANCE_TYPE_REQUIRED_CASH => 'Required Cash',
            self::REBALANCE_TYPE_FULL_AND_TLH => 'Full Rebalance & Tax Loss Harvest',
            self::REBALANCE_TYPE_NO_ACTIONS => 'No Actions',
            self::REBALANCE_TYPE_INITIAL => 'Initial Rebalance',
        ];
    }

    public static function getRebalanceTypeByIndex($index)
    {
        $choices = self::rebalanceTypeChoices();

        return isset($choices[$index]) ? $choices[$index] : false;
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
     * Set name.
     *
     * @param string $name
     *
     * @return Job
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setNameRebalancer()
    {
        $this->setName(self::JOB_NAME_REBALANCER);
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
     * Set started_at.
     *
     * @param \DateTime $startedAt
     *
     * @return Job
     */
    public function setStartedAt($startedAt)
    {
        $this->started_at = $startedAt;

        return $this;
    }

    /**
     * Get started_at.
     *
     * @return \DateTime
     */
    public function getStartedAt()
    {
        return $this->started_at;
    }

    /**
     * Set finished_at.
     *
     * @param \DateTime $finishedAt
     *
     * @return Job
     */
    public function setFinishedAt($finishedAt)
    {
        $this->finished_at = $finishedAt;

        return $this;
    }

    /**
     * Get finished_at.
     *
     * @return \DateTime
     */
    public function getFinishedAt()
    {
        return $this->finished_at;
    }

    /**
     * Set is_error.
     *
     * @param bool $isError
     *
     * @return Job
     */
    public function setIsError($isError)
    {
        $this->is_error = $isError;

        return $this;
    }

    /**
     * Get is_error.
     *
     * @return bool
     */
    public function getIsError()
    {
        return $this->is_error;
    }

    /**
     * Set user.
     *
     * @param \App\Entity\User $user
     *
     * @return Job
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \App\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add rebalancerActions.
     *
     * @param \App\Entity\RebalancerAction $rebalancerActions
     *
     * @return Job
     */
    public function addRebalancerAction(RebalancerAction $rebalancerActions)
    {
        $this->rebalancerActions[] = $rebalancerActions;

        return $this;
    }

    /**
     * Remove rebalancerActions.
     *
     * @param \App\Entity\RebalancerAction $rebalancerActions
     */
    public function removeRebalancerAction(RebalancerAction $rebalancerActions)
    {
        $this->rebalancerActions->removeElement($rebalancerActions);
    }

    /**
     * Get rebalancerActions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRebalancerActions()
    {
        return $this->rebalancerActions;
    }

    /**
     * Set rebalance_type.
     *
     * @param int $rebalanceType
     *
     * @return Job
     */
    public function setRebalanceType($rebalanceType)
    {
        $this->rebalance_type = $rebalanceType;

        return $this;
    }

    /**
     * Get rebalance_type.
     *
     * @return int
     */
    public function getRebalanceType()
    {
        return $this->rebalance_type;
    }
}
