<?php

namespace Model\Pas;

class Transaction extends Base
{
    const TRANSACTION_CODE_BUY  = 'BUY';
    const TRANSACTION_CODE_SELL = 'SELL';
    const TRANSACTION_CODE_MFEE = 'MFEE';
    const TRANSACTION_CODE_MF   = 'IDA12';
    const CLOSING_METHOD_NAME   = 'None';

    protected $lotId;
    protected $accountId;
    protected $securityId;
    protected $closingMethodId;
    protected $transactionTypeId;
    protected $netAmount;
    protected $grossAmount;
    protected $qty;
    protected $txDate;
    protected $settleDate;
    protected $accruedInterest;
    protected $notes;
    protected $cancelStatus;
    protected $status;
    protected $transactionCode;

    public function __construct()
    {
        $this->status = 'verified';
    }

    public function isCreateLot()
    {
        return ($this->transactionCode == self::TRANSACTION_CODE_BUY || $this->transactionCode == self::TRANSACTION_CODE_SELL);
    }

    public function isMFEE()
    {
        return $this->transactionCode == self::TRANSACTION_CODE_MFEE;
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

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setCancelStatus($cancelStatus)
    {
        $this->cancelStatus = $cancelStatus;

        return $this;
    }

    public function getCancelStatus()
    {
        return $this->cancelStatus;
    }

    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function setAccruedInterest($accruedInterest)
    {
        $this->accruedInterest = $accruedInterest;

        return $this;
    }

    public function getAccruedInterest()
    {
        return $this->accruedInterest;
    }

    public function setSettleDate($settleDate)
    {
        $this->settleDate = $settleDate;

        return $this;
    }

    public function getSettleDate()
    {
        return $this->settleDate;
    }

    public function setTxDate($txDate)
    {
        $this->txDate = $txDate;

        return $this;
    }

    public function getTxDate()
    {
        return $this->txDate;
    }

    public function getTxDateAsDateTime()
    {
        return new \DateTime($this->txDate);
    }

    public function setQty($qty)
    {
        $this->qty = $qty;

        return $this;
    }

    public function getQty()
    {
        return $this->qty;
    }

    public function setGrossAmount($grossAmount)
    {
        $this->grossAmount = $grossAmount;

        return $this;
    }

    public function getGrossAmount()
    {
        return $this->grossAmount;
    }

    public function setNetAmount($netAmount)
    {
        $this->netAmount = $netAmount;

        return $this;
    }

    public function getNetAmount()
    {
        return (float) $this->netAmount;
    }

    public function setLotId($lotId)
    {
        $this->lotId = $lotId;

        return $this;
    }

    public function getLotId()
    {
        return $this->lotId;
    }

    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    public function getAccountId()
    {
        return $this->accountId;
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

    public function setClosingMethodId($closingMethodId)
    {
        $this->closingMethodId = $closingMethodId;

        return $this;
    }

    public function getClosingMethodId()
    {
        return $this->closingMethodId;
    }

    public function setTransactionTypeId($transactionTypeId)
    {
        $this->transactionTypeId = $transactionTypeId;

        return $this;
    }

    public function getTransactionTypeId()
    {
        return $this->transactionTypeId;
    }

}