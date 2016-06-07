<?php

namespace Wealthbot\AdminBundle\Entity;

/**
 * RebalancerActionLog.
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
     * @var \Wealthbot\AdminBundle\Entity\RebalancerAction
     */
    private $rebalancerAction;

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
     * @param \Wealthbot\AdminBundle\Entity\RebalancerAction $rebalancerAction
     *
     * @return RebalancerActionLog
     */
    public function setRebalancerAction(\Wealthbot\AdminBundle\Entity\RebalancerAction $rebalancerAction = null)
    {
        $this->rebalancerAction = $rebalancerAction;

        return $this;
    }

    /**
     * Get rebalancerAction.
     *
     * @return \Wealthbot\AdminBundle\Entity\RebalancerAction
     */
    public function getRebalancerAction()
    {
        return $this->rebalancerAction;
    }
}
