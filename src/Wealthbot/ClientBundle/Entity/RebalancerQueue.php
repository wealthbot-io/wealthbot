<?php

namespace Wealthbot\ClientBundle\Entity;

/**
 * RebalancerQueue.
 */
class RebalancerQueue
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var string
     */
    private $status;

    /**
     * @var \Wealthbot\ClientBundle\Entity\Lot
     */
    private $lot;

    /**
     * @var \Wealthbot\AdminBundle\Entity\Security
     */
    private $security;

    /**
     * @var \Wealthbot\ClientBundle\Entity\SystemAccount
     */
    private $systemClientAccount;

    /**
     * @var \Wealthbot\AdminBundle\Entity\RebalancerAction
     */
    private $rebalancerAction;

    /**
     * @var \Wealthbot\AdminBundle\Entity\Subclass
     */
    private $subclass;

    /**
     * @var bool
     */
    private $is_deleted;

    const STATUS_SELL = 'sell';
    const STATUS_BUY = 'buy';

    public function __construct()
    {
        $this->is_deleted = false;
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
     * Set quantity.
     *
     * @param int $quantity
     *
     * @return RebalancerQueue
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity.
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return RebalancerQueue
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set lot.
     *
     * @param \Wealthbot\ClientBundle\Entity\Lot $lot
     *
     * @return RebalancerQueue
     */
    public function setLot(\Wealthbot\ClientBundle\Entity\Lot $lot = null)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * Get lot.
     *
     * @return \Wealthbot\ClientBundle\Entity\Lot
     */
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * Set security.
     *
     * @param \Wealthbot\AdminBundle\Entity\Security $security
     *
     * @return RebalancerQueue
     */
    public function setSecurity(\Wealthbot\AdminBundle\Entity\Security $security = null)
    {
        $this->security = $security;

        return $this;
    }

    /**
     * Get security.
     *
     * @return \Wealthbot\AdminBundle\Entity\Security
     */
    public function getSecurity()
    {
        return $this->security;
    }

    /**
     * Set systemClientAccount.
     *
     * @param \Wealthbot\ClientBundle\Entity\SystemAccount $systemClientAccount
     *
     * @return RebalancerQueue
     */
    public function setSystemClientAccount(\Wealthbot\ClientBundle\Entity\SystemAccount $systemClientAccount = null)
    {
        $this->systemClientAccount = $systemClientAccount;

        return $this;
    }

    /**
     * Get systemClientAccount.
     *
     * @return \Wealthbot\ClientBundle\Entity\SystemAccount
     */
    public function getSystemClientAccount()
    {
        return $this->systemClientAccount;
    }

    /**
     * Set amount.
     *
     * @param float $amount
     *
     * @return RebalancerQueue
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set rebalancerAction.
     *
     * @param \Wealthbot\AdminBundle\Entity\RebalancerAction $rebalancerAction
     *
     * @return RebalancerQueue
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

    /**
     * Set subclass.
     *
     * @param \Wealthbot\AdminBundle\Entity\Subclass $subclass
     *
     * @return RebalancerQueue
     */
    public function setSubclass(\Wealthbot\AdminBundle\Entity\Subclass $subclass = null)
    {
        $this->subclass = $subclass;

        return $this;
    }

    /**
     * Get subclass.
     *
     * @return \Wealthbot\AdminBundle\Entity\Subclass
     */
    public function getSubclass()
    {
        return $this->subclass;
    }

    public function isBuy()
    {
        return self::STATUS_BUY === $this->getStatus();
    }

    public function isSell()
    {
        return self::STATUS_SELL === $this->getStatus();
    }

    /**
     * Set is_deleted.
     *
     * @param bool $isDeleted
     *
     * @return RebalancerQueue
     */
    public function setIsDeleted($isDeleted)
    {
        $this->is_deleted = $isDeleted;

        return $this;
    }

    /**
     * Get is_deleted.
     *
     * @return bool
     */
    public function getIsDeleted()
    {
        return $this->is_deleted;
    }
}
