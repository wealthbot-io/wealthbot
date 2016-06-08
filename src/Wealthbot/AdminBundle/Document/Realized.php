<?php

namespace Wealthbot\AdminBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="realized")
 */
class Realized
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String(name="account_number")
     */
    protected $accountNumber;

    /**
     * @MongoDB\String(name="close_date")
     */
    protected $closeDate;

    /**
     * @MongoDB\String(name="rec_type")
     */
    protected $recType;

    /**
     * @MongoDB\String(name="open_date")
     */
    protected $openDate;

    /**
     * @MongoDB\String(name="cusip_number")
     */
    protected $cusipNumber;

    /**
     * @MongoDB\String(name="ticker_symbol")
     */
    protected $tickerSymbol;

    /**
     * @MongoDB\String(name="security")
     */
    protected $security;

    /**
     * @MongoDB\String(name="shares_sold")
     */
    protected $sharesSold;

    /**
     * @MongoDB\String(name="proceeds")
     */
    protected $proceeds;

    /**
     * @MongoDB\String(name="cost")
     */
    protected $cost;

    /**
     * @MongoDB\String(name="st_gain_loss")
     */
    protected $stGainLoss;

    /**
     * @MongoDB\String(name="lt_gain_loss")
     */
    protected $ltGainLoss;

    /**
     * @MongoDB\String(name="trading_method")
     */
    protected $tradingMethod;

    /**
     * @MongoDB\String(name="settle_date")
     */
    protected $settleDate;

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
     * @const
     */
    const STATUS_NOT_MATCH = 1;
    const STATUS_MATCH = 2;

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
     * @param mixed $closeDate
     */
    public function setCloseDate($closeDate)
    {
        $this->closeDate = $closeDate;
    }

    /**
     * @return mixed
     */
    public function getCloseDate()
    {
        return $this->closeDate;
    }

    /**
     * @param mixed $recType
     */
    public function setRecType($recType)
    {
        $this->recType = $recType;
    }

    /**
     * @return mixed
     */
    public function getRecType()
    {
        return $this->recType;
    }

    /**
     * @param mixed $openDate
     */
    public function setOpenDate($openDate)
    {
        $this->openDate = $openDate;
    }

    /**
     * @return mixed
     */
    public function getOpenDate()
    {
        return $this->openDate;
    }

    /**
     * @param mixed $cusipNumber
     */
    public function setCusipNumber($cusipNumber)
    {
        $this->cusipNumber = $cusipNumber;
    }

    /**
     * @return mixed
     */
    public function getCusipNumber()
    {
        return $this->cusipNumber;
    }

    /**
     * @param mixed $tickerSymbol
     */
    public function setTickerSymbol($tickerSymbol)
    {
        $this->tickerSymbol = $tickerSymbol;
    }

    /**
     * @return mixed
     */
    public function getTickerSymbol()
    {
        return $this->tickerSymbol;
    }

    /**
     * @param mixed $security
     */
    public function setSecurity($security)
    {
        $this->security = $security;
    }

    /**
     * @return mixed
     */
    public function getSecurity()
    {
        return $this->security;
    }

    /**
     * @param mixed $sharesSold
     */
    public function setSharesSold($sharesSold)
    {
        $this->sharesSold = $sharesSold;
    }

    /**
     * @return mixed
     */
    public function getSharesSold()
    {
        return $this->sharesSold;
    }

    /**
     * @param mixed $proceeds
     */
    public function setProceeds($proceeds)
    {
        $this->proceeds = $proceeds;
    }

    /**
     * @return mixed
     */
    public function getProceeds()
    {
        return $this->proceeds;
    }

    /**
     * @param mixed $cost
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    /**
     * @return mixed
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param mixed $stGainLoss
     */
    public function setStGainLoss($stGainLoss)
    {
        $this->stGainLoss = $stGainLoss;
    }

    /**
     * @return mixed
     */
    public function getStGainLoss()
    {
        return $this->stGainLoss;
    }

    /**
     * @param mixed $ltGainLoss
     */
    public function setLtGainLoss($ltGainLoss)
    {
        $this->ltGainLoss = $ltGainLoss;
    }

    /**
     * @return mixed
     */
    public function getLtGainLoss()
    {
        return $this->ltGainLoss;
    }

    /**
     * @param mixed $tradingMethod
     */
    public function setTradingMethod($tradingMethod)
    {
        $this->tradingMethod = $tradingMethod;
    }

    /**
     * @return mixed
     */
    public function getTradingMethod()
    {
        return $this->tradingMethod;
    }
}
