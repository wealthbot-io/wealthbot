<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class RebalancerActionLog
 * @package App\Entity
 */
class RebalancerActionLog
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $action_name;

    /**
     * @var string
     */
    private $result;

    /**
     * @param \App\Entity\RebalancerAction
     */
    private $rebalancerAction;


    /**
     * @var ArrayCollection
     */
    private $rebalancerQueues;

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
     * Set action_name.
     *
     * @param string $actionName
     *
     * @return RebalancerActionLog
     */
    public function setActionName($actionName)
    {
        $this->action_name = $actionName;

        return $this;
    }

    /**
     * Get action_name.
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->action_name;
    }

    /**
     * Set result.
     *
     * @param string $result
     *
     * @return RebalancerActionLog
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get result.
     *
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set rebalancerAction.
     *
     * @param \App\Entity\RebalancerAction $rebalancerAction
     *
     * @return RebalancerActionLog
     */
    public function setRebalancerAction(RebalancerAction $rebalancerAction = null)
    {
        $this->rebalancerAction = $rebalancerAction;

        return $this;
    }

    /**
     * Get rebalancerAction.
     *
     * @return \App\Entity\RebalancerAction
     */
    public function getRebalancerAction()
    {
        return $this->rebalancerAction;
    }

    public function __construct()
    {
        $this->rebalancerQueues = new ArrayCollection();
    }

    public function getRebalancerQueues(): ?RebalancerAction
    {
        return $this->rebalancerQueues;
    }

    public function setRebalancerQueues(?RebalancerAction $rebalancerQueues): self
    {
        $this->rebalancerQueues = $rebalancerQueues;

        return $this;
    }
}
