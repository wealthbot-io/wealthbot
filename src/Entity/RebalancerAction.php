<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class RebalancerAction
 * @package App\Entity
 */
class RebalancerAction
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
     * @param \App\Entity\Job
     */
    private $job;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $rebalancerActionLogs;

    /**
     * @param \App\Entity\ClientAccountValue
     */
    private $clientAccountValue;

    /**
     * @param \App\Entity\ClientPortfolioValue
     */
    private $clientPortfolioValue;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $rebalancerQueue;


    private $systemClientAccount;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->is_error = false;
        $this->started_at = new \DateTime();
        $this->rebalancerActionLogs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rebalancerQueue = new ArrayCollection();
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
     * @return RebalancerAction
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
     * Set started_at.
     *
     * @param \DateTime $startedAt
     *
     * @return RebalancerAction
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
     * @return RebalancerAction
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
     * @return RebalancerAction
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
     * Set job.
     *
     * @param \App\Entity\Job $job
     *
     * @return RebalancerAction
     */
    public function setJob(Job $job = null)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * Get job.
     *
     * @return \App\Entity\Job
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * Add rebalancerActionLogs.
     *
     * @param \App\Entity\RebalancerActionLog $rebalancerActionLogs
     *
     * @return RebalancerAction
     */
    public function addRebalancerActionLog(RebalancerActionLog $rebalancerActionLogs)
    {
        $this->rebalancerActionLogs[] = $rebalancerActionLogs;

        return $this;
    }

    /**
     * Remove rebalancerActionLogs.
     *
     * @param \App\Entity\RebalancerActionLog $rebalancerActionLogs
     */
    public function removeRebalancerActionLog(RebalancerActionLog $rebalancerActionLogs)
    {
        $this->rebalancerActionLogs->removeElement($rebalancerActionLogs);
    }

    /**
     * Get rebalancerActionLogs.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRebalancerActionLogs()
    {
        return $this->rebalancerActionLogs;
    }

    /**
     * Set clientAccountValue.
     *
     * @param \App\Entity\ClientAccountValue $clientAccountValue
     *
     * @return RebalancerAction
     */
    public function setClientAccountValue(ClientAccountValue $clientAccountValue = null)
    {
        $this->clientAccountValue = $clientAccountValue;

        return $this;
    }

    /**
     * Get clientAccountValue.
     *
     * @return \App\Entity\ClientAccountValue
     */
    public function getClientAccountValue()
    {
        return $this->clientAccountValue;
    }

    /**
     * Set clientPortfolioValue.
     *
     * @param \App\Entity\ClientPortfolioValue $clientPortfolioValue
     *
     * @return RebalancerAction
     */
    public function setClientPortfolioValue(ClientPortfolioValue $clientPortfolioValue = null)
    {
        $this->clientPortfolioValue = $clientPortfolioValue;

        return $this;
    }

    /**
     * Get clientPortfolioValue.
     *
     * @return \App\Entity\ClientPortfolioValue
     */
    public function getClientPortfolioValue()
    {
        return $this->clientPortfolioValue;
    }

    /**
     * @var int
     */
    private $status;

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return RebalancerAction
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getStatusToString()
    {
        return Job::getRebalanceTypeByIndex($this->getStatus());
    }

    /**
     * Add rebalancerQueue.
     *
     * @param \App\Entity\RebalancerActionLog $rebalancerQueue
     *
     * @return RebalancerAction
     */
    public function addRebalancerQueue(RebalancerActionLog $rebalancerQueue)
    {
        $this->rebalancerQueue[] = $rebalancerQueue;

        return $this;
    }

    /**
     * Remove rebalancerQueue.
     *
     * @param \App\Entity\RebalancerActionLog $rebalancerQueue
     */
    public function removeRebalancerQueue(RebalancerActionLog $rebalancerQueue)
    {
        $this->rebalancerQueue->removeElement($rebalancerQueue);
    }

    /**
     * Get rebalancerQueue.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRebalancerQueue()
    {
        return $this->rebalancerQueue;
    }

    public function isRebalanced()
    {
        return null !== $this->status;
    }

    public function getSystemClientAccount(): ?SystemAccount
    {
        return $this->systemClientAccount;
    }

    public function setSystemClientAccount(?SystemAccount $systemClientAccount): self
    {
        $this->systemClientAccount = $systemClientAccount;

        return $this;
    }
}
