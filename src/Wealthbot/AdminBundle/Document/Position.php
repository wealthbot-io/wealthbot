<?php

namespace Wealthbot\AdminBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="positions")
 */
class Position
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Int(name="account_number")
     */
    protected $accountNumber;

    /**
     * @MongoDB\String(name="account_type")
     */
    protected $accountType;

    /**
     * @MongoDB\String(name="security_type")
     */
    protected $securityType;

    /**
     * @MongoDB\String(name="symbol")
     */
    protected $symbol;

    /**
     * @MongoDB\Float(name="qty")
     */
    protected $qty;

    /**
     * @MongoDB\String(name="amount")
     */
    protected $amount;

    /**
     * @MongoDB\String(name="ce_shares")
     */
    protected $ceShares;

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
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
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
     * @param mixed $securityType
     */
    public function setSecurityType($securityType)
    {
        $this->securityType = $securityType;
    }

    /**
     * @return mixed
     */
    public function getSecurityType()
    {
        return $this->securityType;
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
     * @param mixed $ceShares
     */
    public function setCeShares($ceShares)
    {
        $this->ceShares = $ceShares;
    }

    /**
     * @return mixed
     */
    public function getCeShares()
    {
        return $this->ceShares;
    }
}
