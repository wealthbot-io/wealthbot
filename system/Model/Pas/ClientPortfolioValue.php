<?php

namespace Model\Pas;

class ClientPortfolioValue extends Base
{
    protected $clientPortfolioId;
    protected $totalValue;
    protected $totalInSecurities;
    protected $totalCashInAccounts;
    protected $totalCashInMoneyMarket;
    protected $sasCash;
    protected $cashBuffer;
    protected $billingCash;
    protected $date;

    public function __construct()
    {
        $this->sasCash = 0;
        $this->cashBuffer = 0;
        $this->billingCash = 0;
        $this->totalValue = 0;
        $this->totalInSecurities = 0;
        $this->totalCashInAccounts = 0;
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

    public function getTotalValue()
    {
        return $this->totalValue;
    }

    public function setTotalValue($totalValue)
    {
        $this->totalValue = $totalValue;

        return $this;
    }

    public function getTotalCashInAccounts()
    {
        return $this->totalCashInAccounts;
    }

    public function setTotalCashInAccounts($totalCashInAccounts)
    {
        $this->totalCashInAccounts = $totalCashInAccounts;

        return $this;
    }

    public function getSasCash()
    {
        return $this->sasCash;
    }

    public function setSasCash($sasCash)
    {
        $this->sasCash = $sasCash;

        return $this;
    }

    public function getCashBuffer()
    {
        return $this->cashBuffer;
    }

    public function setCashBuffer($cashBuffer)
    {
        $this->cashBuffer = $cashBuffer;

        return $this;
    }

    public function getBillingCash()
    {
        return $this->billingCash;
    }

    public function setBillingCash($billingCash)
    {
        $this->billingCash = $billingCash;

        return $this;
    }
}
