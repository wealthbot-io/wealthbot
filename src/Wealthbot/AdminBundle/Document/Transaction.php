<?php

namespace Wealthbot\AdminBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="transactions")
 */
class Transaction
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String(name="advisor_code")
     */
    protected $advisorCode;

    /**
     * @MongoDB\String(name="file_date")
     */
    protected $fileDate;

    /**
     * @MongoDB\Int(name="account_number")
     */
    protected $accountNumber;

    /**
     * @MongoDB\String(name="transaction_code")
     */
    protected $transactionCode;

    /**
     * @MongoDB\String(name="cancel_flag")
     */
    protected $cancelFlag;

    /**
     * @MongoDB\String(name="symbol")
     */
    protected $symbol;

    /**
     * @MongoDB\String(name="security_code")
     */
    protected $securityCode;

    /**
     * @MongoDB\String(name="tx_date")
     */
    protected $txDate;

    /**
     * @MongoDB\String(name="qty")
     */
    protected $qty;

    /**
     * @MongoDB\Float(name="gross_amount")
     */
    protected $grossAmount;

    /**
     * @MongoDB\Float(name="net_amount")
     */
    protected $netAmount;

    /**
     * @MongoDB\String(name="fee")
     */
    protected $fee;

    /**
     * @MongoDB\Int(name="additional_fee")
     */
    protected $additionalFee;

    /**
     * @MongoDB\String(name="settle_date")
     */
    protected $settleDate;

    /**
     * @MongoDB\String(name="transfer_account")
     */
    protected $transferAccount;

    /**
     * @MongoDB\String(name="account_type")
     */
    protected $accountType;

    /**
     * @MongoDB\String(name="accrued_interest")
     */
    protected $accruedInterest;

    /**
     * @MongoDB\String(name="closing_method")
     */
    protected $closingMethod;

    /**
     * @MongoDB\String(name="notes")
     */
    protected $notes;

    /**
     * @MongoDB\String(name="created")
     */
    protected $created;

    /**
     * @MongoDB\String(name="import_date")
     */
    protected $importDate;

    /**
     * @MongoDB\String(name="source")
     */
    protected $source;

    /**
     * @MongoDB\Int(name="status")
     */
    protected $status;

    /**
     * @MongoDB\String(name="username")
     */
    protected $username;

    const STATUS_NOT_POSTED = 1;
    const STATUS_POSTED = 2;
    const STATUS_CANCELLED = 3;

    public function __construct()
    {
        $this->setGrossAmount(0.0);
        $this->setClosingMethod('None');
        $this->setCancelFlag(' ');
        $this->setAccruedInterest(' ');
    }

    /**
     * @param mixed $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return mixed
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param mixed $accountType
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
    }

    /**
     * @return mixed
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @param mixed $accruedInterest
     */
    public function setAccruedInterest($accruedInterest)
    {
        $this->accruedInterest = $accruedInterest;
    }

    /**
     * @return mixed
     */
    public function getAccruedInterest()
    {
        return $this->accruedInterest;
    }

    /**
     * @param mixed $additionalFee
     */
    public function setAdditionalFee($additionalFee)
    {
        $this->additionalFee = $additionalFee;
    }

    /**
     * @return mixed
     */
    public function getAdditionalFee()
    {
        return $this->additionalFee;
    }

    /**
     * @param mixed $advisorCode
     */
    public function setAdvisorCode($advisorCode)
    {
        $this->advisorCode = $advisorCode;
    }

    /**
     * @return mixed
     */
    public function getAdvisorCode()
    {
        return $this->advisorCode;
    }

    /**
     * @param mixed $cancelFlag
     */
    public function setCancelFlag($cancelFlag)
    {
        $this->cancelFlag = $cancelFlag;
    }

    /**
     * @return mixed
     */
    public function getCancelFlag()
    {
        return $this->cancelFlag;
    }

    /**
     * @param mixed $closingMethod
     */
    public function setClosingMethod($closingMethod)
    {
        $this->closingMethod = $closingMethod;
    }

    /**
     * @return mixed
     */
    public function getClosingMethod()
    {
        return $this->closingMethod;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $fee
     */
    public function setFee($fee)
    {
        $this->fee = $fee;
    }

    /**
     * @return mixed
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * @param mixed $fileDate
     */
    public function setFileDate($fileDate)
    {
        $this->fileDate = $fileDate;
    }

    /**
     * @return mixed
     */
    public function getFileDate()
    {
        return $this->fileDate;
    }

    /**
     * @param mixed $grossAmount
     */
    public function setGrossAmount($grossAmount)
    {
        $this->grossAmount = $grossAmount;
    }

    /**
     * @return mixed
     */
    public function getGrossAmount()
    {
        return $this->grossAmount;
    }

    /**
     * @param mixed $netAmount
     */
    public function setNetAmount($netAmount)
    {
        $this->netAmount = $netAmount;
    }

    /**
     * @return mixed
     */
    public function getNetAmount()
    {
        return $this->netAmount;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $importDate
     */
    public function setImportDate($importDate)
    {
        $this->importDate = $importDate;
    }

    /**
     * @return mixed
     */
    public function getImportDate()
    {
        return $this->importDate;
    }

    /**
     * @param mixed $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param mixed $qty
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    }

    /**
     * @return mixed
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param mixed $securityCode
     */
    public function setSecurityCode($securityCode)
    {
        $this->securityCode = $securityCode;
    }

    /**
     * @return mixed
     */
    public function getSecurityCode()
    {
        return $this->securityCode;
    }

    /**
     * @param mixed $settleDate
     */
    public function setSettleDate($settleDate)
    {
        $this->settleDate = $settleDate;
    }

    /**
     * @return mixed
     */
    public function getSettleDate()
    {
        return $this->settleDate;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $symbol
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;
    }

    /**
     * @return mixed
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * @param mixed $transactionCode
     */
    public function setTransactionCode($transactionCode)
    {
        $this->transactionCode = $transactionCode;
    }

    /**
     * @return mixed
     */
    public function getTransactionCode()
    {
        return $this->transactionCode;
    }

    /**
     * @param mixed $transferAccount
     */
    public function setTransferAccount($transferAccount)
    {
        $this->transferAccount = $transferAccount;
    }

    /**
     * @return mixed
     */
    public function getTransferAccount()
    {
        return $this->transferAccount;
    }

    /**
     * @param mixed $txDate
     */
    public function setTxDate($txDate)
    {
        $this->txDate = $txDate;
    }

    /**
     * @return mixed
     */
    public function getTxDate()
    {
        return $this->txDate;
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
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return int
     */
    public function getUsername()
    {
        return $this->username;
    }
}
