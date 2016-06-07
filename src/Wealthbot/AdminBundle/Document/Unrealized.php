<?php

namespace Wealthbot\AdminBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="unrealized")
 */
class Unrealized
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String(name="custodial_id")
     */
    protected $custodialId;

    /**
     * @MongoDB\String(name="business_date")
     */
    protected $businessDate;

    /**
     * @MongoDB\String(name="account_number")
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
     * @MongoDB\String(name="current_qty")
     */
    protected $currentQty;

    /**
     * @MongoDB\String(name="cost_basis_un")
     */
    protected $costBasisUn;

    /**
     * @MongoDB\String(name="cost_basis_am")
     */
    protected $costBasisAm;

    /**
     * @MongoDB\String(name="unrealized_gain_loss")
     */
    protected $unrealizedGainLoss;

    /**
     * @MongoDB\String(name="cost_basis_fully_nnown")
     */
    protected $costBasisFullyNnown;

    /**
     * @MongoDB\String(name="certified_flag")
     */
    protected $certifiedFlag;

    /**
     * @MongoDB\String(name="original_purchase_date")
     */
    protected $originalPurchaseDate;

    /**
     * @MongoDB\String(name="original_purchase_price")
     */
    protected $originalPurchasePrice;

    /**
     * @MongoDB\String(name="wash_sale_indicator")
     */
    protected $washSaleIndicator;

    /**
     * @MongoDB\String(name="wash_sale_qty")
     */
    protected $washSaleQty;

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
     * @MongoDB\String(name="status")
     */
    protected $status;

    /**
     * @MongoDB\String(name="lot_date")
     */
    protected $lotDate;

    /**
     * @MongoDB\String(name="lot_amount")
     */
    protected $lotAmount;

    /**
     * @MongoDB\String(name="lot_quantity")
     */
    protected $lotQuantity;

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
     * @param int $custodialId
     */
    public function setCustodialId($custodialId)
    {
        $this->custodialId = $custodialId;
    }

    /**
     * @return mixed
     */
    public function getCustodialId()
    {
        return $this->custodialId;
    }

    /**
     * @param int $businessDate
     */
    public function setBusinessDate($businessDate)
    {
        $this->businessDate = $businessDate;
    }

    /**
     * @return mixed
     */
    public function getBusinessDate()
    {
        return $this->businessDate;
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
     * @param mixed $currentQty
     */
    public function setCurrentQty($currentQty)
    {
        $this->currentQty = $currentQty;
    }

    /**
     * @return mixed
     */
    public function getCurrentQty()
    {
        return $this->currentQty;
    }

    /**
     * @param mixed $costBasisUn
     */
    public function setCostBasisUn($costBasisUn)
    {
        $this->costBasisUn = $costBasisUn;
    }

    /**
     * @return mixed
     */
    public function getCostBasisUn()
    {
        return $this->costBasisUn;
    }

    /**
     * @param mixed $costBasisAm
     */
    public function setCostBasisAm($costBasisAm)
    {
        $this->costBasisAm = $costBasisAm;
    }

    /**
     * @return mixed
     */
    public function getCostBasisAm()
    {
        return $this->costBasisAm;
    }

    /**
     * @param mixed $unrealizedGainLoss
     */
    public function setUnrealizedGainLoss($unrealizedGainLoss)
    {
        $this->unrealizedGainLoss = $unrealizedGainLoss;
    }

    /**
     * @return mixed
     */
    public function getUnrealizedGainLoss()
    {
        return $this->unrealizedGainLoss;
    }

    /**
     * @param mixed $costBasisFullyNnown
     */
    public function setCostBasisFullyNnown($costBasisFullyNnown)
    {
        $this->costBasisFullyNnown = $costBasisFullyNnown;
    }

    /**
     * @return mixed
     */
    public function getCostBasisFullyNnown()
    {
        return $this->costBasisFullyNnown;
    }

    /**
     * @param mixed $certifiedFlag
     */
    public function setCertifiedFlag($certifiedFlag)
    {
        $this->certifiedFlag = $certifiedFlag;
    }

    /**
     * @return mixed
     */
    public function getCertifiedFlag()
    {
        return $this->certifiedFlag;
    }

    /**
     * @param mixed $originalPurchaseDate
     */
    public function setOriginalPurchaseDate($originalPurchaseDate)
    {
        $this->originalPurchaseDate = $originalPurchaseDate;
    }

    /**
     * @return mixed
     */
    public function getOriginalPurchaseDate()
    {
        return $this->originalPurchaseDate;
    }

    /**
     * @param mixed $originalPurchasePrice
     */
    public function setOriginalPurchasePrice($originalPurchasePrice)
    {
        $this->originalPurchasePrice = $originalPurchasePrice;
    }

    /**
     * @return mixed
     */
    public function getOriginalPurchasePrice()
    {
        return $this->originalPurchasePrice;
    }

    /**
     * @param mixed $washSaleIndicator
     */
    public function setWashSaleIndicator($washSaleIndicator)
    {
        $this->washSaleIndicator = $washSaleIndicator;
    }

    /**
     * @return mixed
     */
    public function getWashSaleIndicator()
    {
        return $this->washSaleIndicator;
    }

    /**
     * @param mixed $washSaleQty
     */
    public function setWashSaleQty($washSaleQty)
    {
        $this->washSaleQty = $washSaleQty;
    }

    /**
     * @return mixed
     */
    public function getWashSaleQty()
    {
        return $this->washSaleQty;
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
     * @return mixed
     */
    public function getLotDate()
    {
        return $this->lotDate;
    }

    /**
     * @return mixed
     */
    public function getLotAmount()
    {
        return $this->lotAmount;
    }

    /**
     * @return mixed
     */
    public function getLotQuantity()
    {
        return $this->lotQuantity;
    }
}
