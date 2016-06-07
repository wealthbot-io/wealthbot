<?php

namespace Model\Pas;

class ClientAccountValue extends Base
{
    protected $clientPortfolioId;
    protected $systemClientAccountId;
    protected $source;
    protected $totalValue;
    protected $totalInSecurities;
    protected $totalCashInAccount;
    protected $totalCashInMoneyMarket;
    protected $sasCash;
    protected $cashBuffer;
    protected $billingCash;
    protected $date;

    public function __construct()
    {
        $this->source = '';
        $this->sasCash = 0;
        $this->cashBuffer = 0;
        $this->billingCash = 0;
        $this->totalValue = 0;
        $this->totalInSecurities = 0;
        $this->totalCashInAccount = 0;
        $this->totalCashInMoneyMarket = 0;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setClientPortfolioId($clientPortfolioId)
    {
        $this->clientPortfolioId = $clientPortfolioId;
    }

    public function getClientPortfolioId()
    {
        return $this->clientPortfolioId;
    }

    public function setSystemClientAccountId($systemClientAccountId)
    {
        $this->systemClientAccountId = $systemClientAccountId;
    }

    public function getSystemClientAccountId()
    {
        return $this->systemClientAccountId;
    }

    public function setTotalInSecurities($totalInSecurities)
    {
        $this->totalInSecurities = $totalInSecurities;
    }

    public function getTotalInSecurities()
    {
        return $this->totalInSecurities;
    }

    public function setTotalCashInMoneyMarket($totalCashInMoneyMarket)
    {
        $this->totalCashInMoneyMarket = $totalCashInMoneyMarket;
    }

    public function getTotalCashInMoneyMarket()
    {
        return $this->totalCashInMoneyMarket;
    }

    public function setTotalValue($totalValue)
    {
        $this->totalValue = $totalValue;

        return $this;
    }

    public function getTotalValue()
    {
        return $this->totalValue;
    }

    public function getTotalCashInAccount()
    {
        return $this->totalCashInAccount;
    }

    public function getSasCash()
    {
        return $this->sasCash;
    }

    public function getCashBuffer()
    {
        return $this->cashBuffer;
    }

    public function getBillingCash()
    {
        return $this->billingCash;
    }

    public function getSource()
    {
        return $this->source;
    }
}
