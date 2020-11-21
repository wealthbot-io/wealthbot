<?php

namespace App\Entity;

/**
 * Class RebalancerQueue
 * @package App\Entity
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
     * @param \App\Entity\Lot
     */
    private $lot;

    /**
     * @param \App\Entity\Security
     */
    private $security;

    /**
     * @param \App\Entity\SystemAccount
     */
    private $systemClientAccount;

    /**
     * @param \App\Entity\RebalancerAction
     */
    private $rebalancerAction;

    /**
     * @param \App\Entity\Subclass
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
     * @param \App\Entity\Lot $lot
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
     * @return \App\Entity\Lot
     */
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * Set security.
     *
     * @param \App\Entity\Security $security
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
     * @return \App\Entity\Security
     */
    public function getSecurity()
    {
        return $this->security;
    }

    /**
     * Set systemClientAccount.
     *
     * @param \App\Entity\SystemAccount $systemClientAccount
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
     * @return \App\Entity\SystemAccount
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
     * @param \App\Entity\RebalancerAction $rebalancerAction
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
     * @return \App\Entity\RebalancerAction
     */
    public function getRebalancerAction()
    {
        return $this->rebalancerAction;
    }

    /**
     * Set subclass.
     *
     * @param \App\Entity\Subclass $subclass
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
     * @return \App\Entity\Subclass
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
