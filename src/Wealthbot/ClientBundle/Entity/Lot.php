<?php

namespace Wealthbot\ClientBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Wealthbot\AdminBundle\Entity\Security;
use Wealthbot\AdminBundle\Entity\Transaction;

/**
 * Lot contains some count of shares and must be sold fully at one transaction.
 * Set of lots makes Position.
 */
class Lot
{
    const LOT_INITIAL = 1;
    const LOT_IS_OPEN = 2;
    const LOT_CLOSED = 3;
    const LOT_DIVIDED = 4;

    /**
     * @var int
     */
    private $id;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var Transaction[]|ArrayCollection
     */
    private $transactions;

    /**
     * @var int
     */
    private $status;

    /**
     * @var Position
     */
    private $position;

    /**
     * @var float
     */
    private $costBasis;

    /**
     * @var bool
     */
    private $costBasisKnown;

    /**
     * @var bool
     */
    private $washSale;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var SystemAccount
     */
    private $clientSystemAccount;

    /**
     * @var Lot
     */
    private $initial;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var bool
     */
    private $wasClosed;

    /**
     * @var float
     */
    private $realizedGain;

    /**
     * @var bool
     */
    private $wasRebalancerDiff;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->status = self::LOT_IS_OPEN;
        $this->wasClosed = false;
        $this->wasRebalancerDiff = false;
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
     * @param float $quantity
     *
     * @return Lot
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity.
     *
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set amount.
     *
     * @param float $amount
     *
     * @return Lot
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
     * @param Transaction[]|ArrayCollection $transactions
     *
     * @return $this
     */
    public function setTransactions($transactions)
    {
        $this->transactions = $transactions;

        return $this;
    }

    /**
     * @return Transaction[]|ArrayCollection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction)
    {
        $this->transactions->add($transaction);

        return $this;
    }

    public function removeTransaction(Transaction $transaction)
    {
        $this->transactions->remove($transaction);

        return $this;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param \Wealthbot\ClientBundle\Entity\Position $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return \Wealthbot\ClientBundle\Entity\Position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param float $costBasis
     */
    public function setCostBasis($costBasis)
    {
        $this->costBasis = $costBasis;
    }

    /**
     * @return float
     */
    public function getCostBasis()
    {
        return $this->costBasis;
    }

    /**
     * @param bool $costBasisKnown
     */
    public function setCostBasisKnown($costBasisKnown)
    {
        $this->costBasisKnown = $costBasisKnown;
    }

    /**
     * @return bool
     */
    public function isCostBasisKnown()
    {
        return $this->costBasisKnown;
    }

    /**
     * @param bool $washSale
     */
    public function setWashSale($washSale)
    {
        $this->washSale = $washSale;
    }

    /**
     * @return bool
     */
    public function isWashSale()
    {
        return $this->washSale;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Position
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set clientSystemAccount.
     *
     * @param \Wealthbot\ClientBundle\Entity\SystemAccount $clientSystemAccount
     *
     * @return Position
     */
    public function setClientSystemAccount(\Wealthbot\ClientBundle\Entity\SystemAccount $clientSystemAccount = null)
    {
        $this->clientSystemAccount = $clientSystemAccount;

        return $this;
    }

    /**
     * Get clientSystemAccount.
     *
     * @return \Wealthbot\ClientBundle\Entity\SystemAccount
     */
    public function getClientSystemAccount()
    {
        return $this->clientSystemAccount;
    }

    /**
     * Set security.
     *
     * @param \Wealthbot\AdminBundle\Entity\Security $security
     *
     * @return Position
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
     * Set initial.
     *
     * @param \Wealthbot\ClientBundle\Entity\Lot $initial
     *
     * @return Lot
     */
    public function setInitial(\Wealthbot\ClientBundle\Entity\Lot $initial = null)
    {
        $this->initial = $initial;

        return $this;
    }

    /**
     * Get initial.
     *
     * @return \Wealthbot\ClientBundle\Entity\Lot
     */
    public function getInitial()
    {
        return $this->initial;
    }

    /**
     * @param bool $wasClosed
     */
    public function setWasClosed($wasClosed)
    {
        $this->wasClosed = $wasClosed;
    }

    /**
     * @return bool
     */
    public function getWasClosed()
    {
        return $this->wasClosed;
    }

    /**
     * Set realizedGain.
     *
     * @param float $realizedGain
     *
     * @return Lot
     */
    public function setRealizedGain($realizedGain)
    {
        $this->realizedGain = $realizedGain;

        return $this;
    }

    /**
     * Get realizedGain.
     *
     * @return float
     */
    public function getRealizedGain()
    {
        return $this->realizedGain;
    }

    public function getShortTermGain()
    {
        if (!isset($this->initial)) {
            return;
        }
        $difference = $this->date->diff($this->initial->getDate());

        return ($difference > new \DateInterval('P365D')) ? 0 : $this->realizedGain;
    }

    public function getLongTermGain()
    {
        if (!isset($this->initial)) {
            return;
        }
        $difference = $this->date->diff($this->initial->getDate());

        return ($difference > new \DateInterval('P365D')) ? $this->realizedGain : 0;
    }

    /**
     * Get costBasisKnown.
     *
     * @return bool
     */
    public function getCostBasisKnown()
    {
        return $this->costBasisKnown;
    }

    /**
     * Get washSale.
     *
     * @return bool
     */
    public function getWashSale()
    {
        return $this->washSale;
    }

    /**
     * Get wasRebalancerDiff.
     *
     * @return bool
     */
    public function getWasRebalancerDiff()
    {
        return $this->wasRebalancerDiff;
    }

    /**
     * @param $wasRebalancerDiff
     *
     * @return $this
     */
    public function setWasRebalancerDiff($wasRebalancerDiff)
    {
        $this->wasRebalancerDiff = $wasRebalancerDiff;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShortTerm()
    {
        $today = new \DateTime();

        if ($this->getInitial()) {
            $difference = $today->diff($this->initial->getDate());
        } else {
            $difference = $today->diff($this->getDate());
        }

        return $difference->d <= 365;
    }
}
