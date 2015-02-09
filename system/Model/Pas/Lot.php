<?php

namespace Model\Pas;

use Wealthbot\ClientBundle\Entity\Lot as WealthbotLot;

class Lot extends Base
{
    /**
     * @var  float
     */
    protected $amount;

    protected $costBasis;

    /**
     * @var  float
     */
    protected $quantity;

    /**
     * @var  string
     */
    protected $date;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var int
     */
    protected $initialLotId;

    /**
     * @var int
     */
    protected $positionId;

    /**
     * is the lot current open or closed?
     * @var bool
     */
    protected $wasClosed;

    /**
     * @var float
     */
    protected $realizedGain;

    protected $symbol;
    protected $transactionCode;
    protected $securityId;
    protected $clientSystemAccountId;
    protected $wasRebalancerDiff;

    public function __construct()
    {
        $this->realizedGain = 0;
    }

    public function setWasRebalancerDiff($wasRebalancerDiff)
    {
        $this->wasRebalancerDiff = $wasRebalancerDiff;

        return $this;
    }

    public function getWasRebalancerDiff()
    {
        return $this->wasRebalancerDiff;
    }

    public function setClientSystemAccountId($clientSystemAccountId)
    {
        $this->clientSystemAccountId = $clientSystemAccountId;

        return $this;
    }

    public function getClientSystemAccountId()
    {
        return $this->clientSystemAccountId;
    }

    public function setSecurityId($securityId)
    {
        $this->securityId = $securityId;

        return $this;
    }

    public function getSecurityId()
    {
        return $this->securityId;
    }

    public function isMF()
    {
        return $this->symbol == Transaction::TRANSACTION_CODE_MF;
    }

    public function isBuy()
    {
        return $this->transactionCode == Transaction::TRANSACTION_CODE_BUY;
    }

    public function isSell()
    {
        return $this->transactionCode == Transaction::TRANSACTION_CODE_SELL;
    }

    /**
     * @param string $symbol
     * @return $this
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return float
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * @param string $transactionCode
     * @return $this
     */
    public function setTransactionCode($transactionCode)
    {
        $this->transactionCode = $transactionCode;

        return $this;
    }

    /**
     * @return float
     */
    public function getTransactionCode()
    {
        return $this->transactionCode;
    }

    public function setCostBasis($costBasis)
    {
        $this->costBasis = $costBasis;

        return $this;
    }

    public function getCostBasis()
    {
        return $this->costBasis;
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return (float) $this->amount;
    }


    /**
     * @param int $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Is status initial
     *
     * @return bool
     */
    public function isInitial()
    {
        return ($this->status == WealthbotLot::LOT_INITIAL);
    }

    /**
     * Is status open
     *
     * @return bool
     */
    public function isOpen()
    {
        return ($this->status == WealthbotLot::LOT_IS_OPEN);
    }

    /**
     * Is status closed
     *
     * @return bool
     */
    public function isClosed()
    {
        return ($this->status == WealthbotLot::LOT_CLOSED);
    }

    public function calcPrice()
    {
        if (!$this->quantity) {
            return null;
        }

        return $this->amount / $this->quantity;
    }

    /**
     * @param bool $wasClosed
     * @return $this
     */
    public function setWasClosed($wasClosed)
    {
        $this->wasClosed = $wasClosed;

        return $this;
    }

    /**
     * @return bool
     */
    public function getWasClosed()
    {
        return $this->wasClosed;
    }

    /**
     * @param float $realizedGain
     * @return $this
     */
    public function setRealizedGain($realizedGain)
    {
        $this->realizedGain = $realizedGain;

        return $this;
    }

    /**
     * @return float
     */
    public function getRealizedGain()
    {
        return $this->realizedGain;
    }

    /**
     * @param int $initialLotId
     * @return $this
     */
    public function setInitialLotId($initialLotId)
    {
        $this->initialLotId = $initialLotId;

        return $this;
    }

    /**
     * @return int
     */
    public function getInitialLotId()
    {
        return $this->initialLotId;
    }

    /**
     * @param int $positionId
     * @return $this
     */
    public function setPositionId($positionId)
    {
        $this->positionId = $positionId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPositionId()
    {
        return $this->positionId;
    }
}