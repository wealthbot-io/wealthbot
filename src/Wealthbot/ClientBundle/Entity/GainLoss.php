<?php

namespace Wealthbot\ClientBundle\Entity;

/**
 * GainLoss.
 */
class GainLoss
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $client_system_account_id;

    /**
     * @var int
     */
    private $transaction_id;

    /**
     * @var int
     */
    private $security_id;

    /**
     * @var float
     */
    private $qty;

    /**
     * @var float
     */
    private $cost_basis;

    /**
     * @var float
     */
    private $value;

    /**
     * @var int
     */
    private $is_cost_basis_known;

    /**
     * @var int
     */
    private $is_wash_sale;

    /**
     * @var \DateTime
     */
    private $date;

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
     * Set client_system_account_id.
     *
     * @param int $clientSystemAccountId
     *
     * @return this
     */
    public function setClientSystemAccountId($clientSystemAccountId)
    {
        $this->client_system_account_id = $clientSystemAccountId;

        return $this;
    }

    /**
     * Get client_system_account_id.
     *
     * @return int
     */
    public function getClientSystemAccountId()
    {
        return $this->client_system_account_id;
    }

    /**
     * Set transaction_id.
     *
     * @param int $transactionId
     *
     * @return this
     */
    public function setTransactionId($transactionId)
    {
        $this->transaction_id = $transactionId;

        return $this;
    }

    /**
     * Get transaction_id.
     *
     * @return int
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * Set security_id.
     *
     * @param int $securityId
     *
     * @return this
     */
    public function setSecurityId($securityId)
    {
        $this->security_id = $securityId;

        return $this;
    }

    /**
     * Get security_id.
     *
     * @return int
     */
    public function getSecurityId()
    {
        return $this->security_id;
    }

    /**
     * Set qty.
     *
     * @param float $qty
     *
     * @return this
     */
    public function setQty($qty)
    {
        $this->qty = $qty;

        return $this;
    }

    /**
     * Get qty.
     *
     * @return float
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * Set cost_basis.
     *
     * @param float $costBasis
     *
     * @return this
     */
    public function setCostBasis($costBasis)
    {
        $this->cost_basis = $costBasis;

        return $this;
    }

    /**
     * Get cost_basis.
     *
     * @return float
     */
    public function getCostBasis()
    {
        return $this->cost_basis;
    }

    /**
     * Set value.
     *
     * @param float $value
     *
     * @return this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set is cost basis known.
     *
     * @param int $isCostBasisKnown
     *
     * @return this
     */
    public function setIsCostBasisKnown($isCostBasisKnown)
    {
        $this->is_cost_basis_known = $isCostBasisKnown;

        return $this;
    }

    /**
     * Get is cost basis known.
     *
     * @return int
     */
    public function getIsCostBasisKnown()
    {
        return $this->is_cost_basis_known;
    }

    /**
     * Set is wash sale.
     *
     * @param int $isWashSale
     *
     * @return this
     */
    public function setIsWashSale($isWashSale)
    {
        $this->is_wash_sale = $isWashSale;

        return $this;
    }

    /**
     * Get is wash sale.
     *
     * @return int
     */
    public function getIsWashSale()
    {
        return $this->is_wash_sale;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return this
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
     * @var \Wealthbot\ClientBundle\Entity\SystemAccount
     */
    private $clientSystemAccount;

    /**
     * @var \Wealthbot\AdminBundle\Entity\Transaction
     */
    private $transaction;

    /**
     * @var \Wealthbot\AdminBundle\Entity\Security
     */
    private $security;

    /**
     * Set clientSystemAccount.
     *
     * @param \Wealthbot\ClientBundle\Entity\SystemAccount $clientSystemAccount
     *
     * @return this
     */
    public function setClientSystemAccount(\Wealthbot\ClientBundle\Entity\SystemAccount $clientSystemAccount = null)
    {
        $this->clientSystemAccount = $clientSystemAccount;

        return $this;
    }

    /**
     * Get clientSystemAccount.
     *
     * @return \Wealthbot\ClientBundle\Entity\SystemAccount
     */
    public function getClientSystemAccount()
    {
        return $this->clientSystemAccount;
    }

    /**
     * Set transaction.
     *
     * @param \Wealthbot\AdminBundle\Entity\Transaction $transaction
     *
     * @return this
     */
    public function setTransaction(\Wealthbot\AdminBundle\Entity\Transaction $transaction = null)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Get transaction.
     *
     * @return \Wealthbot\AdminBundle\Entity\Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Set security.
     *
     * @param \Wealthbot\AdminBundle\Entity\Security $security
     *
     * @return this
     */
    public function setSecurity(\Wealthbot\AdminBundle\Entity\Security $security = null)
    {
        $this->security = $security;

        return $this;
    }

    /**
     * Get security.
     *
     * @return \Wealthbot\AdminBundle\Entity\Security
     */
    public function getSecurity()
    {
        return $this->security;
    }
}
