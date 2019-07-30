<?php

namespace App\Entity;

use App\Entity\RebalancerAction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class ClientPortfolioValue
 * @package App\Entity
 */
class ClientPortfolioValue
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $client_portfolio_id;

    /**
     * @var float
     */
    private $total_value;

    /**
     * @var float
     */
    private $total_in_securities;

    /**
     * @var float
     */
    private $total_cash_in_accounts;

    /**
     * @var float
     */
    private $total_cash_in_money_market;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @param \App\Entity\ClientPortfolio
     */
    private $clientPortfolio;

    /**
     * @var float
     */
    private $sas_cash;

    /**
     * @var float
     */
    private $cash_buffer;

    /**
     * @var float
     */
    private $billing_cash;

    /**
     * @var float
     */
    private $model_deviation;

    /**
     * @var float
     */
    private $required_cash;

    /**
     * @var float
     */
    private $investable_cash;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $rebalancerActions;

    /**
     * @var bool
     */
    private $reconciled;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->rebalancerActions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set client_portfolio_id.
     *
     * @param int $clientPortfolioId
     *
     * @return ClientPortfolioValue
     */
    public function setClientPortfolioId($clientPortfolioId)
    {
        $this->client_portfolio_id = $clientPortfolioId;

        return $this;
    }

    /**
     * Get client_portfolio_id.
     *
     * @return int
     */
    public function getClientPortfolioId()
    {
        return $this->client_portfolio_id;
    }

    /**
     * Set total_value.
     *
     * @param float $totalValue
     *
     * @return ClientPortfolioValue
     */
    public function setTotalValue($totalValue)
    {
        $this->total_value = $totalValue;

        return $this;
    }

    /**
     * Get total_value.
     *
     * @return float
     */
    public function getTotalValue()
    {
        return $this->total_value;
    }

    /**
     * Set total_in_securities.
     *
     * @param float $totalInSecurities
     *
     * @return ClientPortfolioValue
     */
    public function setTotalInSecurities($totalInSecurities)
    {
        $this->total_in_securities = $totalInSecurities;

        return $this;
    }

    /**
     * Get total_in_securities.
     *
     * @return float
     */
    public function getTotalInSecurities()
    {
        return $this->total_in_securities;
    }

    /**
     * Set total_cash_in_accounts.
     *
     * @param float $totalCashInAccounts
     *
     * @return ClientPortfolioValue
     */
    public function setTotalCashInAccounts($totalCashInAccounts)
    {
        $this->total_cash_in_accounts = $totalCashInAccounts;

        return $this;
    }

    /**
     * Get total_cash_in_accounts.
     *
     * @return float
     */
    public function getTotalCashInAccounts()
    {
        return $this->total_cash_in_accounts;
    }

    /**
     * Set total_cash_in_money_market.
     *
     * @param float $totalCashInMoneyMarket
     *
     * @return ClientPortfolioValue
     */
    public function setTotalCashInMoneyMarket($totalCashInMoneyMarket)
    {
        $this->total_cash_in_money_market = $totalCashInMoneyMarket;

        return $this;
    }

    /**
     * Get total_cash_in_money_market.
     *
     * @return float
     */
    public function getTotalCashInMoneyMarket()
    {
        return $this->total_cash_in_money_market;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return ClientPortfolioValue
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
     * Set clientPortfolio.
     *
     * @param \App\Entity\ClientPortfolio $clientPortfolio
     *
     * @return ClientPortfolioValue
     */
    public function setClientPortfolio(ClientPortfolio $clientPortfolio = null)
    {
        $this->clientPortfolio = $clientPortfolio;

        return $this;
    }

    /**
     * Get clientPortfolio.
     *
     * @return \App\Entity\ClientPortfolio
     */
    public function getClientPortfolio()
    {
        return $this->clientPortfolio;
    }

    /**
     * Set sas_cash.
     *
     * @param float $sasCash
     *
     * @return ClientPortfolioValue
     */
    public function setSasCash($sasCash)
    {
        $this->sas_cash = $sasCash;

        return $this;
    }

    /**
     * Get sas_cash.
     *
     * @return float
     */
    public function getSasCash()
    {
        return $this->sas_cash;
    }

    /**
     * Set cash_buffer.
     *
     * @param float $cashBuffer
     *
     * @return ClientPortfolioValue
     */
    public function setCashBuffer($cashBuffer)
    {
        $this->cash_buffer = $cashBuffer;

        return $this;
    }

    /**
     * Get cash_buffer.
     *
     * @return float
     */
    public function getCashBuffer()
    {
        return $this->cash_buffer;
    }

    /**
     * Set billing_cash.
     *
     * @param float $billingCash
     *
     * @return ClientPortfolioValue
     */
    public function setBillingCash($billingCash)
    {
        $this->billing_cash = $billingCash;

        return $this;
    }

    /**
     * Get billing_cash.
     *
     * @return float
     */
    public function getBillingCash()
    {
        return $this->billing_cash;
    }

    /**
     * Set model_deviation.
     *
     * @param float $modelDeviation
     *
     * @return ClientPortfolioValue
     */
    public function setModelDeviation($modelDeviation)
    {
        $this->model_deviation = $modelDeviation;

        return $this;
    }

    /**
     * Get model_deviation.
     *
     * @return float
     */
    public function getModelDeviation()
    {
        return $this->model_deviation;
    }

    /**
     * Set required_cash.
     *
     * @param float $requiredCash
     *
     * @return ClientPortfolioValue
     */
    public function setRequiredCash($requiredCash)
    {
        $this->required_cash = $requiredCash;

        return $this;
    }

    /**
     * Get required_cash.
     *
     * @return float
     */
    public function getRequiredCash()
    {
        return $this->required_cash;
    }

    /**
     * Set investable_cash.
     *
     * @param float $investableCash
     *
     * @return ClientPortfolioValue
     */
    public function setInvestableCash($investableCash)
    {
        $this->investable_cash = $investableCash;

        return $this;
    }

    /**
     * Get investable_cash.
     *
     * @return float
     */
    public function getInvestableCash()
    {
        return $this->investable_cash;
    }

    /**
     * Add rebalancerActions.
     *
     * @param \App\Entity\RebalancerAction $rebalancerActions
     *
     * @return ClientPortfolioValue
     */
    public function addRebalancerAction(RebalancerAction $rebalancerActions)
    {
        $this->rebalancerActions[] = $rebalancerActions;

        return $this;
    }

    /**
     * Remove rebalancerActions.
     *
     * @param \App\Entity\RebalancerAction $rebalancerActions
     */
    public function removeRebalancerAction(RebalancerAction $rebalancerActions)
    {
        $this->rebalancerActions->removeElement($rebalancerActions);
    }

    /**
     * Get rebalancerActions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRebalancerActions()
    {
        return $this->rebalancerActions;
    }

    /**
     * @return RebalancerAction
     */
    public function getRebalancerAction()
    {
        return $this->rebalancerActions->last();
    }

    /**
     * Set reconciled.
     *
     * @param bool $reconciled
     *
     * @return ClientPortfolioValue
     */
    public function setReconciled($reconciled)
    {
        $this->reconciled = $reconciled;

        return $this;
    }

    /**
     * Get reconciled.
     *
     * @return bool
     */
    public function getReconciled()
    {
        return $this->reconciled;
    }
}
