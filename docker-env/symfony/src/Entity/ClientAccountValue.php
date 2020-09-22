<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class ClientAccountValue
 * @package App\Entity
 */
class ClientAccountValue
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $portfolio_id;

    /**
     * @var string
     */
    private $source;

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
    private $total_cash_in_account;

    /**
     * @var float
     */
    private $total_cash_in_money_market;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var int
     */
    private $client_portfolio_id;

    /**
     * @param \App\Entity\ClientPortfolio
     */
    private $clientPortfolio;

    /**
     * @var int
     */
    private $system_client_account_id;

    /**
     * @param \App\Entity\SystemAccount
     */
    private $systemClientAccount;

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
     * Set portfolio_id.
     *
     * @param int $portfolioId
     *
     * @return ClientAccountValue
     */
    public function setPortfolioId($portfolioId)
    {
        $this->portfolio_id = $portfolioId;

        return $this;
    }

    /**
     * Get portfolio_id.
     *
     * @return int
     */
    public function getPortfolioId()
    {
        return $this->portfolio_id;
    }

    /**
     * Set source.
     *
     * @param string $source
     *
     * @return ClientAccountValue
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set total_value.
     *
     * @param float $totalValue
     *
     * @return ClientAccountValue
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
     * @return ClientAccountValue
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
     * Set total_cash_in_account.
     *
     * @param float $totalCashInAccount
     *
     * @return ClientAccountValue
     */
    public function setTotalCashInAccount($totalCashInAccount)
    {
        $this->total_cash_in_account = $totalCashInAccount;

        return $this;
    }

    /**
     * Get total_cash_in_account.
     *
     * @return float
     */
    public function getTotalCashInAccount()
    {
        return $this->total_cash_in_account;
    }

    /**
     * Set total_cash_in_money_market.
     *
     * @param float $totalCashInMoneyMarket
     *
     * @return ClientAccountValue
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
     * @return ClientAccountValue
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
     * Set client_portfolio_id.
     *
     * @param int $clientPortfolioId
     *
     * @return ClientAccountValue
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
     * Set client_portfolio.
     *
     * @param \App\Entity\ClientPortfolio $clientPortfolio
     *
     * @return ClientAccountValue
     */
    public function setClientPortfolio(ClientPortfolio $clientPortfolio = null)
    {
        $this->clientPortfolio = $clientPortfolio;

        return $this;
    }

    /**
     * Get client_portfolio.
     *
     * @return \App\Entity\ClientPortfolio
     */
    public function getClientPortfolio()
    {
        return $this->clientPortfolio;
    }

    /**
     * Set system_client_account_id.
     *
     * @param int $systemClientAccountId
     *
     * @return ClientAccountValue
     */
    public function setSystemClientAccountId($systemClientAccountId)
    {
        $this->system_client_account_id = $systemClientAccountId;

        return $this;
    }

    /**
     * Get system_client_account_id.
     *
     * @return int
     */
    public function getSystemClientAccountId()
    {
        return $this->system_client_account_id;
    }

    /**
     * Set systemClientAccount.
     *
     * @param \App\Entity\SystemAccount $systemClientAccount
     *
     * @return ClientAccountValue
     */
    public function setSystemClientAccount(SystemAccount $systemClientAccount = null)
    {
        $this->systemClientAccount = $systemClientAccount;

        return $this;
    }

    /**
     * Get systemClientAccount.
     *
     * @return \App\Entity\SystemAccount
     */
    public function getSystemClientAccount()
    {
        return $this->systemClientAccount;
    }

    /**
     * Set sas_cash.
     *
     * @param float $sasCash
     *
     * @return ClientAccountValue
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
     * @return ClientAccountValue
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
     * @return ClientAccountValue
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
     * @return ClientAccountValue
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
     * @return ClientAccountValue
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
     * @return ClientAccountValue
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
     * @return ClientAccountValue
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

    public function getRebalancerAction()
    {
        return $this->rebalancerActions->last();
    }

    /**
     * Set reconciled.
     *
     * @param bool $reconciled
     *
     * @return ClientAccountValue
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
