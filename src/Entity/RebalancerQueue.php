<?php

namespace App\Entity;

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
     * @var \Entity\Lot
     */
    private $lot;

    /**
     * @var \Entity\Security
     */
    private $security;

    /**
     * @var \Entity\SystemAccount
     */
    private $systemClientAccount;

    /**
     * @var \Entity\RebalancerAction
     */
    private $rebalancerAction;

    /**
     * @var \Entity\Subclass
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
     * @param \Entity\Lot $lot
     *
     * @return RebalancerQueue
     */
    public function setLot(Lot $lot = null)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * Get lot.
     *
     * @return \Entity\Lot
     */
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * Set security.
     *
     * @param \Entity\Security $security
     *
     * @return RebalancerQueue
     */
    public function setSecurity(Security $security = null)
    {
        $this->security = $security;

        return $this;
    }

    /**
     * Get security.
     *
     * @return \Entity\Security
     */
    public function getSecurity()
    {
        return $this->security;
    }

    /**
     * Set systemClientAccount.
     *
     * @param \Entity\SystemAccount $systemClientAccount
     *
     * @return RebalancerQueue
     */
    public function setSystemClientAccount(SystemAccount $systemClientAccount = null)
    {
        $this->systemClientAccount = $systemClientAccount;

        return $this;
    }

    /**
     * Get systemClientAccount.
     *
     * @return \Entity\SystemAccount
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
     * @param \Entity\RebalancerAction $rebalancerAction
     *
     * @return RebalancerQueue
     */
    public function setRebalancerAction(RebalancerAction $rebalancerAction = null)
    {
        $this->rebalancerAction = $rebalancerAction;

        return $this;
    }

    /**
     * Get rebalancerAction.
     *
     * @return \Entity\RebalancerAction
     */
    public function getRebalancerAction()
    {
        return $this->rebalancerAction;
    }

    /**
     * Set subclass.
     *
     * @param \Entity\Subclass $subclass
     *
     * @return RebalancerQueue
     */
    public function setSubclass(Subclass $subclass = null)
    {
        $this->subclass = $subclass;

        return $this;
    }

    /**
     * Get subclass.
     *
     * @return \Entity\Subclass
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
